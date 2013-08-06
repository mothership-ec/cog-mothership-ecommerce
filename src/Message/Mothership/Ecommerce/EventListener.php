<?php

namespace Message\Mothership\Ecommerce;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

class EventListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			BuildMenuEvent::BUILD_MAIN_MENU => array(
				array('registerMainMenuItems'),
			),
		);
	}

	public function registerMainMenuItems(BuildMenuEvent $event)
	{
		//$event->addItem('ms.ecom.fulfillment', 'Fulfillment', array('ms.ecom'));
	}
}