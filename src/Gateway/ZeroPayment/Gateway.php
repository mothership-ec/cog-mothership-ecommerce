<?php

namespace Message\Mothership\Ecommerce\Gateway\ZeroPayment;

class Gateway implements GatewayInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'zero-payment';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPurchaseControllerReference()
	{
		return 'Message:Mothership:Ecommerce::Controller:Gateway:ZeroPayment#purchase';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRefundControllerReference()
	{
		return 'Message:Mothership:Ecommerce::Controller:Gateway:ZeroPayment#refund';
	}
}