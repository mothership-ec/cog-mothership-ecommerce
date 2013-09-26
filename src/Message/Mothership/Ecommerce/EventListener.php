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

	/**
	 * Add extra fields to the impersonate user login form.
	 *
	 * @param  ImpersonateFormEvent $event
	 * @return ImpersonateFormEvent
	 */
	public function registerImpersonateFormFields(ImpersonateFormEvent $event)
	{
		$form = $event->getForm();

		$form->add('place_order', 'checkbox', 'Place an order for this user');

		$event->setForm($form);

		return $event;
	}
}