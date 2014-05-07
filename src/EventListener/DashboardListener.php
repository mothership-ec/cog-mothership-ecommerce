<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Mothership\Commerce\Order\Event;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order\Events as OrderEvents;
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
			OrderEvents::DISPATCH_SHIPPED => array(
				array('recordFulfillmentTime'),
			),
			OrderEvents::DISPATCH_POSTAGE_AUTO => array(
				array('recordFulfillmentTime'),
			),
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

	/**
	 * Record the time it took since the order was created to complete
	 * fulfillment.
	 *
	 * @param  Event\Event $event
	 */
	public function recordFulfillmentTime(Event\Event $event)
	{
		$order = $event->getOrder();

		$this->get('statistics')->get('fulfillment.time')
			->add($order->id, time() - $order->authorship->createdAt()->getTimestamp());
	}
}