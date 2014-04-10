<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\ControlPanel\Event\Dashboard\DashboardEvent;

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
			DashboardEvent::DASHBOARD_INDEX => array(
				'buildDashboardIndex',
			),
			'dashboard.commerce.orders' => array(
				'buildDashboardOrders',
			)
		);
	}

	public function buildDashboardIndex($event)
	{
		$event->addReference('Message:Mothership:Ecommerce::Controller:Module:Dashboard:OrdersFulfillmentTime#index');
	}

	/**
	 * Add controller references to the orders dashboard.
	 *
	 * @param  DashboardEvent $event
	 */
	public function buildDashboardOrders(DashboardEvent $event)
	{
		$event->addReference('Message:Mothership:Ecommerce::Controller:Module:Dashboard:OrdersFulfillmentTime#index');
	}
}