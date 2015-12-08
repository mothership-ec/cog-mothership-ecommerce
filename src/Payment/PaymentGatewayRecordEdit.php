<?php

namespace Message\Mothership\Ecommerce\Payment;

use Message\Mothership\Commerce\Payment\Payment;
use Message\Mothership\Ecommerce\Gateway\GatewayInterface;
use Message\Cog\DB\Query;

/**
 * @author Samuel Trangmar-Keates <sam@message.co.uk>
 *
 * Class for saving and updating payment-gateway mapping records
 */
class PaymentGatewayRecordEdit
{
	/**
	 * @var Query
	 */
	private $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	/**
	 * Saves a payment-gateway mapping
	 * 
	 * @param  Payment          $payment The payment
	 * @param  GatewayInterface $gateway The gateway
	 */
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