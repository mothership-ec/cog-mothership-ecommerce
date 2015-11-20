<?php

namespace Message\Mothership\Ecommerce\Payment;

use Message\Mothership\Commerce\Payment\Payment;
use Message\Mothership\Ecommerce\Gateway\GatewayInterface;
use Message\Mothership\Commerce\Payment;
use Message\Cog\DB\Query;

/**
 * @author Samuel Trangmar-Keates <sam@message.co.uk>
 */
class PaymentGatewayRecordLoader
{
	private $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function save(Payment $payment, GatewayInterface $gateway)
	{
		if (!$payment->id) {
			throw new \LogicException("Payment ID must be set before saving payment gateway record.");
		}

		$this->_query->run("REPLACE INTO 
				`payment_gateway`
			VALUES
				(?i, ?s)
		", [
			$payment->id,
			$gateway->getName()
		]);
	}
}