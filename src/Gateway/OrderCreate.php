<?php

class OrderCreate
{
	public function __construct(Order\Create $create, GatewayInterface $gateway)
	{

	}

	public function setPaymentMethod(MethodInteface $method)
	{

	}

	public function setPaymentAmount($amount)
	{

	}

	public function setUser($user)
	{

	}

	public function create()
	{
		// add the payment to the order
		// check it has been paid in full
		//
	}

	public function getConfirmationUri()
	{

	}
}