<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\User\Event as UserEvents;
use Symfony\Component\HttpKernel\HttpKernel;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
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
		//$event->setResponse(new RedirectResponse('new URL'));
	}
}