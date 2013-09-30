<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\User\Event as UserEvents;
use Symfony\Component\HttpKernel\HttpKernel;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Message\Cog\HTTP\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

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
		return array(KernelEvents::RESPONSE => array(
			array('routeUser')
		));
	}

	public function routeUser(FilterResponseEvent $event)
	{

		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}

		$route = $event->getRequest()->attributes->get('_route');
		$collections = $event->getRequest()->attributes->get('_route_collections');

		if (!in_array('ms.ecom.checkout',$collections)) {
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
				$route = $url->generate('ms.ecom.checkout.details');

				return true;
			}

			$addresses = $this->get('commerce.user.address.loader')->getByUser($user);

			if ($user instanceof \Message\User\User && $addresses) {
				// Route to the delivery stage
				$route = $url->generate('ms.ecom.checkout.confirm');
			}

			if ($user instanceof \Message\User\User && !$addresses) {
				// Route to the update addresses page
				$route = $url->generate('ms.ecom.checkout.details.addresses');
			}

		 	return $event->setResponse(new RedirectResponse($route));
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
	}
}