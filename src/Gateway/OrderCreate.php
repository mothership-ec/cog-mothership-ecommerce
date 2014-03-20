<?php

namespace Message\Mothership\Ecommerce\Gateway;

/**
 * Helper for creating orders while handling payments through a gateway.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class OrderCreate
{
	/**
	 * Order creator.
	 *
	 * @var Order\Create
	 */
	protected $_create;

	/**
	 * Payment gateway.
	 *
	 * @var GatewayInterface
	 */
	protected $_gateway;

	/**
	 * Payment method.
	 *
	 * @var MethodInterface
	 */
	protected $_method;

	/**
	 * Payment amount.
	 *
	 * @var float
	 */
	protected $_amount;

	/**
	 * Customer placing the order.
	 *
	 * @var UserInterface
	 */
	protected $_user;

	/**
	 * Set the order creator and gateway for use in the `create()` method.
	 *
	 * @param OrderCreate      $create
	 * @param GatewayInterface $gateway
	 */
	public function __construct(Order\Create $create, GatewayInterface $gateway)
	{
		$this->_create  = $create;
		$this->_gateway = $gateway;
	}

	/**
	 * Set the payment method.
	 *
	 * @param MethodInterface $method
	 */
	public function setPaymentMethod(MethodInterface $method)
	{
		$this->_method = $method;
	}

	/**
	 * Set the payment amount.
	 *
	 * @param float $amount
	 */
	public function setPaymentAmount($amount)
	{
		$this->_amount = $amount;
	}

	/**
	 * Set the order user.
	 *
	 * @param UserInterface $user
	 */
	public function setUser(UserInterface $user)
	{
		$this->_user = $user;
	}

	/**
	 * Create the order.
	 *
	 * @return Order
	 */
	public function create()
	{
		// where does the original $order come from?

		$order = $this->_create
			->setUser($this->_user)
			->create($order);
	}
}