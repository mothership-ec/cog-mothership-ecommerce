<?php

namespace Message\Mothership\Ecommerce\Gateway\ZeroPayment;

use Message\Mothership\Ecommerce\Gateway\GatewayInterface;

/**
 * Zero payment dummy gateway.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
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