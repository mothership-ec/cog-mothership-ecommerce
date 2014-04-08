<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order;

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
			'dashboard.references' => array(
				array('dashboardReferences'),
			)
		);
	}

	public function dashboardReferences($event)
	{
		$event->addReference('Message:Mothership:Ecommerce::Controller:Module:Dashboard:OrdersFulfillmentTime#index');
	}
}