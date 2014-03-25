<?php

namespace Message\Mothership\Ecommerce\Gateway\LocalPayment;

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