<?php

namespace Message\Mothership\Ecommerce\Payment;

use Message\Mothership\Ecommerce\Gateway\Collection as GatewayCollection;
use Message\Mothership\Ecommerce\Gateway\GatewayInterface;
use Message\Mothership\Commerce\Payment;
use Message\Cog\DB\QueryBuilderFactory;

/**
 * @author Samuel Trangmar-Keates <sam@message.co.uk>
 *
 * Loads payment-gateway records. Either the gateway for an payment or all payments
 * created using a gateway.
 */
class PaymentGatewayRecordLoader
{
	private $_queryBuilderFactory;
	private $_gatewayCollection;
	private $_paymentLoader;

	public function __construct(Payment\Loader $paymentLoader, GatewayCollection $gatewayCollection, QueryBuilderFactory $qbf)
	{
		$this->_queryBuilderFactory = $qbf;
		$this->_gatewayCollection = $gatewayCollection;
		$this->_paymentLoader = $paymentLoader;
	}

	/**
	 * Gets a the gateway based on the payment
	 * 
	 * @param  Payment\Payment $payment The payment
	 * @return GatewayInterface         The found gateway
	 */
	public function getGatewayByPayment(Payment\Payment $payment)
	{
		$result = $this->_queryBuilderFactory->getQueryBuilder()
			->select('`gateway`')
			->from('`payment_gateway`')
			->where('`payment_id` = ?i', [
				$payment->id
			])
			->run();

		if (!$result->count() || !$result->value()) {
			throw new \GatewayNotFoundException("No record found for the given payment.");
		}

		$result = $result->value();

		return $this->_gatewayCollection->get($result);
	}

	/**
	 * Gets the payments created through a gateway.
	 * 
	 * @param  GatewayInterface       $gateway The gateway
	 * @return array[Payment\Payment]          The payments for the gateway
	 */
	public function getPaymentsByGateway(GatewayInterface $gateway)
	{
		$result = $this->_queryBuilderFactory->getQueryBuilder()
			->select('`payment_id`')
			->from('`payment_gateway`')
			->where('`gateway` = ?s', [
				$gateway->getName()
			]);

		return $this->_paymentLoader->getByIDs($result->flatten());
	}
}