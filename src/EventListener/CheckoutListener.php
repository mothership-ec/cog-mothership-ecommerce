<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Mothership\Ecommerce\Event as EcommerceEvent;
use Message\Mothership\Commerce\Order\Event\Event as OrderEvent;
use Message\Mothership\CMS\Analytics\AnalyticsEditableProviderInterface;
use Message\User\Event as UserEvents;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\HTTP\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * Checkout event listener for deciding where where to route the user
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class CheckoutListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return [
			KernelEvents::RESPONSE => [
				['routeUser']
			],
			EcommerceEvent::ORDER_SUCCESS => [
				['switchAnalyticsCheckoutView']
			]
		];
	}

	/**
	 * @todo break this method up a little bit, it's currently a nightmare to read
	 *
	 * @param FilterResponseEvent $event
	 * @return bool|void
	 */
	public function routeUser(FilterResponseEvent $event)
	{
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}

		$route = $event->getRequest()->attributes->get('_route');
		$collections = $event->getRequest()->attributes->get('_route_collections');

		if (!is_array($collections) || !in_array('ms.ecom.checkout',$collections)) {
			return;
		}

		if (in_array($route, array(
			'ms.ecom.checkout.payment.successful',
			'ms.ecom.checkout.payment.unsuccessful',
			'ms.ecom.checkout.payment')
		)) {
			return;
		}

		$numItems = count($this->get('basket')->getOrder()->items);
		$url = $this->get('routing.generator');
		$user = $this->get('user.current');

		$allowedRoutes = array(
			'ms.ecom.checkout',
			'ms.ecom.checkout.payment.successful',
			'ms.ecom.checkout.payment.unsuccessful',
			'ms.ecom.checkout.payment.response',
			'ms.ecom.checkout.details.register',
			'ms.ecom.checkout.details.register.process',
			'ms.ecom.basket.empty',
		);
		// Throw users to the first stage of checkout if they don't have any items
		// in their basket unless they are at the first stage OR on the confirmation
		// page as their basket will get emptied
		if ($collections[0] == 'ms.ecom.checkout' && $numItems == 0 && !in_array($route, $allowedRoutes)) {
			return $event->setResponse(new RedirectResponse($url->generate('ms.ecom.checkout')));
		}

		$allowedRoutes = array(
			'ms.ecom.checkout',
			'ms.ecom.checkout.details',
			'ms.ecom.checkout.details.register',
			'ms.ecom.checkout.details.register.process',
			'ms.ecom.checkout.payment.response',
			'ms.ecom.checkout.remove',
			'ms.ecom.basket.empty',
			'ms.ecom.checkout.action',
		);

		if (!$user instanceof \Message\User\User && $collections[0] == 'ms.ecom.checkout' && !in_array($route, $allowedRoutes)) {
			return $event->setResponse(new RedirectResponse($url->generate('ms.ecom.checkout')));
		}

		// Handles where to throw the user after the first stage of checkout
		if ($route == 'ms.ecom.checkout.details') {

			// Is the user logged in?
			if ($user instanceof \Message\User\AnonymousUser) {
				// Sign up / Register
				return true;
			}

			$addresses = $this->get('commerce.order.user_address.loader')->getByUser($user);

			if ($user instanceof \Message\User\User && $addresses && !count($this->get('basket')->getOrder()->addresses)) {
				$this->get('event.dispatcher')->dispatch(
					UserEvents\Event::LOGIN,
					new UserEvents\Event($user)
				);
			}

			if ($user instanceof \Message\User\User && $addresses && count($this->get('basket')->getOrder()->addresses)) {
				// Route to the delivery stage
				$route = 'ms.ecom.checkout.confirm';
			}

			if ($addresses && !count($this->get('basket')->getOrder()->addresses)) {
				$this->get('basket')->setEntities('addresses', $addresses);
			}

			if ($user instanceof \Message\User\User) {
				if ($addresses) {
					foreach ($addresses as $address) {
						$address->order = $this->get('basket')->getOrder();
					}
					$this->get('basket')->setEntities('addresses', $addresses);
				}
				// Route to the update addresses page
				$route = 'ms.ecom.checkout.details.addresses';
			}

		 	return $event->setResponse(new RedirectResponse($url->generate($route)));
		}

		if ($route == 'ms.ecom.checkout.confirm') {
			$order = $this->get('basket')->getOrder();
			if (count($order->addresses) < 2) {
				$this->get('http.session')->getFlashBag()->add('warning','Please ensure you have both a billing and delivery address set.');
				$route = $url->generate('ms.ecom.checkout.details.addresses');

			 	return $event->setResponse(new RedirectResponse($route));
			}
		}

		if ($route == 'ms.ecom.checkout.payment' && !$this->get('basket')->getOrder()->shippingName) {
			$this->get('http.session')->getFlashBag()->add('warning','You must select a delivery method before continuing.');

			$route = $url->generate('ms.ecom.checkout.confirm');

			return $event->setResponse(new RedirectResponse($route));
		}

		return;
	}

	/**
	 * Alter analytics view in checkout if possible
	 *
	 * @param OrderEvent $event
	 */
	public function switchAnalyticsCheckoutView(OrderEvent $event)
	{
		$provider = $this->get('analytics.provider');

		if (!$provider instanceof AnalyticsEditableProviderInterface) {
			return;
		}

		$viewRef = 'Message:Mothership:Ecommerce::analytics:' . $provider->getName();

		try {
			$this->get('templating.view_name_parser')->parse($viewRef);
		} catch (NotAcceptableHttpException $e) {
			return;
		}

		$parentView = $provider->getViewReference();
		$params = array_merge($provider->getViewParams(), [
			'order' => $event->getOrder(),
			'parentView' => $parentView,
		]);

		$provider->setViewReference($viewRef);
		$provider->setViewParams($params);
	}
}