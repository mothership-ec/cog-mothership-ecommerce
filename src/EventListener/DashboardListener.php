<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\ControlPanel\Event\Event as CPEvent;

/**
 * Dashboard event listener.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class DashboardListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			CPEvent::DASHBOARD_INDEX => array(
				'buildDashboardIndex',
			)
		);
	}

	public function buildDashboardIndex($event)
	{
		$event->addReference('Message:Mothership:Ecommerce::Controller:Module:Dashboard:OrdersFulfillmentTime#index');
	}
}