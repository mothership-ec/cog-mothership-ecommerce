<?php

namespace Message\Mothership\Ecommerce\Gateway\LocalPayment;

use Message\Mothership\Ecommerce\Gateway\GatewayInterface;

class Gateway implements GatewayInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'local-payment';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPurchaseControllerReference()
	{
		return 'Message:Mothership:Ecommerce::Controller:Gateway:LocalPayment#purchase';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRefundControllerReference()
	{
		return 'Message:Mothership:Ecommerce::Controller:Gateway:LocalPayment#refund';
	}
}