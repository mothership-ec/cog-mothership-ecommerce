<?php

namespace Message\Mothership\Ecommerce\Gateway\Sagepay;

use Message\Mothership\Commerce\...\PayableInterface;
use Message\Mothership\Ecommerce\Gateway\GatewayInterface;

/**
 * This SagePay payment gateway integrates with the SagePay Server api via the
 * OmniPay interface.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Gateway implements GatewayInterface;
{
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'sagepay';
	}

	public function purchase(PayableInterface $payable)
	{

	}

	public function refund(PayableInterface $payable)
	{

	}
}