<?php

namespace Message\Mothership\Ecommerce\Gateway\Sagepay;

use Omnipay\Common\GatewayFactory;
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
	const CACHE_PREFIX = 'gateway.adapter.sagepay.response.';

	protected $_server;

	public function __construct($vendor)
	{
		$this->_server = (new GatewayFactory)->create('SagePay_Server');
		$this->_server->setVendor($vendor);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'sagepay';
	}

	public function purchase(PayableInterface $payable)
	{
		$response = $this->_server->purchase([
			'amount'   => $payable->amount,
			'currency' => $payable->currency,
			'card'     => $payable->card,
		])->send();

		if ($response->isRedirect()) {
			$data = [
				'response' => $response->getData(),
				'payable'  => $payable,
			];
			$path = self::CACHE_PREFIX . $data['response']['VPSTxId'];

			$this->_cache->store($path, serialize($data));
		}

		return $response;
	}

	public function completePurchase($transactionID)
	{
		$path = self::CACHE_PREFIX . $transactionID;

		if (! $this->_cache->exists($responseID)) {
			throw new SomeException;
		}

		$data = $this->_cache->fetch($path);
		$this->_cache->delete($path);

		$payable = $data['payable'];

		$response = $this->_server->completePurchase([
			// check which data need to be sent
		])->setTransactionReference()->send();

		return $response;
	}

	public function refund(PayableInterface $payable)
	{
		$response = $this->_server->refund([
			'amount'        => $payable->amount,
			'currency'      => $payable->currency,
			'description'   => 'Refund...',
			'transactionId' => ...,
		])->send();

		return $response;
	}
}