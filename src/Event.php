<?php

namespace Message\Mothership\Ecommerce;

class Event
{
	const EMPTY_BASKET           = 'ecommerce.basket.empty';
	const ORDER_SUCCESS          = 'ms.ecommerce.checkout.success';
	const FULFILLMENT_MENU_BUILD = 'ms.ecommerce.fulfillment.menu.build';

	/**
	 * Get the user relating to this event.
	 *
	 * er.. what is this? surely it never does anything?
	 *
	 * @return User
	 */
	public function getUser()
	{
		return $this->_user;
	}
}