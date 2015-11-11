<?php

namespace Message\Mothership\Ecommerce\Checkout;

use Message\Cog\Event\Event as BaseEvent;
use Message\Mothership\Commerce\Order\Order;

/**
 * Class Event
 * @package Message\Mothership\Ecommerce\Checkout
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Event to be fired at various stages throughout checkout. This allows event listeners in Mothership
 * installations to make additional alterations to the order going through checkout.
 */
class Event extends BaseEvent
{
	/**
	 * @var Order
	 */
	private $_order;

	/**
	 * @var array
	 */
	private $_data;

	/**
	 * @param Order $order
	 * @param array $data
	 */
	public function __construct(Order $order, array $data = [])
	{
		$this->setOrder($order);
		$this->setData($data);
	}

	/**
	 * @param Order $order
	 */
	public function setOrder(Order $order)
	{
		$this->_order = $order;
	}

	/**
	 * @return Order
	 */
	public function getOrder()
	{
		return $this->_order;
	}

	/**
	 * @param array $data
	 */
	public function setData(array $data)
	{
		$this->_data = $data;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->_data;
	}
}