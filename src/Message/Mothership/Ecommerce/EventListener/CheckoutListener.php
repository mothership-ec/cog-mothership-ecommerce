<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\User\Event as UserEvents;
use Symfony\Component\HttpKernel\HttpKernel;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Message\Cog\HTTP\RedirectResponse;

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
		return array(KernelEvents::REQUEST => array(
			array('routeUser')
		));
	}

	public function routeUser(GetResponseEvent $event)
	{
		$route = $event->getRequest()->attributes->get('_route');

		if ($route == 'ms.ecom.checkout.details') {
			$user = $this->get('user.current');
			$url = $this->get('routing.generator');
			// Is the user logged in?
			if ($user instanceof \Message\User\AnonymousUser) {
				// Sign up / Register
				$route = $url->generate('ms.ecom.checkout.account');

				return $event->setResponse(new RedirectResponse($route));
			}

			$addresses = $this->get('commerce.user.loader')->getByUser($user);

			if ($user instanceof \Message\User\User && $addresses) {
				// Route to the delivery stage
				$route = $url->generate('ms.ecom.checkout.delivery');
			}

			if ($user instanceof \Message\User\User && !$addresses) {
				// Route to the update addresses page
				$route = $url->generate('ms.ecom.checkout.details.addresses');
			}

		 	return $event->setResponse(new RedirectResponse($route));
		}
	}
}