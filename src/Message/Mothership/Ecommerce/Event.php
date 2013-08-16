<?php

namespace Message\Mothership\Ecommerce;

class Event
{
	const EMPTY_BASKET = 'ecommerce.basket.empty';

	/**
	 * Get the user relating to this event.
	 *
	 * @return User
	 */
	public function getUser()
	{
		return $this->_user;
	}
}