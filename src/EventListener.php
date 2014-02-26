<?php

namespace Message\Mothership\Ecommerce;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;
use Message\Mothership\Commerce\Order\Events;
use Message\Mothership\User\Event\ImpersonateFormEvent;

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
			Events::BUILD_ORDER_SIDEBAR => array(
				array('registerSidebarItems'),
			),
			Event::FULFILLMENT_MENU_BUILD => array(
				array('registerFulfillmentMenu'),
			),
			'ms.cp.user.impersonate.form.build' => array(
				array('registerImpersonateFormFields')
			),
		);
	}

	public function registerMainMenuItems(BuildMenuEvent $event)
	{
		//$event->addItem('ms.ecom.fulfillment', 'Fulfillment', array('ms.ecom'));
	}

	/**
	 * Register items to the sidebar of the orders-pages.
	 *
	 * @param BuildMenuEvent $event The event
	 */
	public function registerSidebarItems(BuildMenuEvent $event)
	{
		$event->addItem('ms.ecom.fulfillment.active', 'Fulfillment');
	}

	public function registerFulfillmentMenu(BuildMenuEvent $event)
	{
		$event->addItem('ms.ecom.fulfillment.active', 'Active');

		$event->addItem('ms.ecom.fulfillment.new', 'New');

		$event->addItem('ms.ecom.fulfillment.pick', 'Pick', [
			'ms.ecom.fulfillment.process.pick',
			'ms.ecom.fulfillment.process.pick.action'
		]);

		$event->addItem('ms.ecom.fulfillment.pack', 'Pack', [
			'ms.ecom.fulfillment.process.pack',
			'ms.ecom.fulfillment.process.pack.action'
		]);

		$event->addItem('ms.ecom.fulfillment.post', 'Post', [
			'ms.ecom.fulfillment.process.post',
			'ms.ecom.fulfillment.process.post.action'
		]);

		$event->addItem('ms.ecom.fulfillment.pickup', 'Pick up');
	}

	/**
	 * Add extra fields to the impersonate user login form.
	 *
	 * @param  ImpersonateFormEvent $event
	 * @return ImpersonateFormEvent
	 */
	public function registerImpersonateFormFields(ImpersonateFormEvent $event)
	{
		$form = $event->getForm();

		$form->add('order_skip_payment', 'checkbox', 'Skip payment when placing an order for this user')
			->val()
			->optional();

		$event->setForm($form);

		return $event;
	}
}