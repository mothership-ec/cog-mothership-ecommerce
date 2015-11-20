<?php

namespace Message\Mothership\Ecommerce\Payment;

use Message\Mothership\Commerce\Payment\Payment;
use Message\Mothership\Ecommerce\Gateway\Collection as GatewayCollection;
use Message\Mothership\Ecommerce\Gateway\GatewayInterface;
use Message\Mothership\Commerce\Payment;

/**
 * @author Samuel Trangmar-Keates <sam@message.co.uk>
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

	public function getGatewayByPayment(Payment $payment)
	{
		$result = $this->_queryBuilderFactory->getQueryBuilder()
			->select('`gateway`')
			->from('`payment_gateway`')
			->where('`payment_id` = ?i', [
				$payment->id
			]);

		if (!$result->count() || !$result->value()) {
			throw new \GatewayNotFoundException("No record found for the given payment.");
		}

		$result = $result->value();

		return $this->_gatewayCollection->get($result);
	}

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