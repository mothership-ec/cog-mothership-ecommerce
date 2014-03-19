<?php

namespace Message\Mothership\Ecommerce\Gateway\Sagepay;

use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayFactory;
use Omnipay\SagePay\ServerGateway;
use Message\Cog\Cache\CacheInterface;
use Message\Mothership\Commerce\...\PayableInterface;
use Message\Mothership\Ecommerce\Gateway\GatewayInterface;

/**
 * This SagePay payment gateway integrates with the SagePay Server api via the
 * OmniPay interface.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Gateway implements GatewayInterface
{
	/**
	 * Prefix for the cache name.
	 */
	const CACHE_PREFIX = 'gateway.sagepay.purchase.';

	/**
	 * OmniPay gateway for handling calls to SagePay's API.
	 *
	 * @var SagePay_Server
	 */
	protected $_server;

	/**
	 * Cache for storing payment data between requests.
	 *
	 * @var CacheInterface
	 */
	protected $_cache;

	/**
	 * Constructor.
	 *
	 * @param ServerGateway  $server
	 * @param CacheInterface $cache
	 */
	public function __construct(ServerGateway $server, CacheInterface $cache)
	{
		$this->_server = $server;
		$this->_cache  = $cache;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'sagepay';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPaymentControllerReference()
	{
		return 'Message:Mothership:Ecommerce::Controller:Gateway:Sagepay#purchase';
	}

	/**
	 * Attempt a purchase against a payable with a card. If the response is an
	 * external redirect, store the response data and payable in the cache
	 * for use with the callback after the external payment is made.
	 *
	 * @param  PayableInterface $payable
	 * @param  CreditCard       $card
	 * @return \Omnipay\SagePay\Message\Response
	 */
	public function purchase(PayableInterface $payable, CreditCard $card)
	{
		$response = $this->_server->purchase([
			'amount'   => $payable->amount,
			'currency' => $payable->currency,
			'card'     => $card,
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

	/**
	 * Attempt to complete a purchase during the callback from an external
	 * payment. The previous response data and payable are retrieved
	 * from the cache against the transaction ID.
	 *
	 * @param  string $transactionID
	 * @return \Omnipay\SagePay\Message\Response
	 */
	public function completePurchase($transactionID)
	{
		$path = self::CACHE_PREFIX . $transactionID;

		if (! $this->_cache->exists($responseID)) {
			throw new SomeException;
		}

		$data = $this->_cache->fetch($path);
		$this->_cache->delete($path);

		$response = $this->_server->completePurchase([
			// check which data need to be sent
		])->setTransactionReference()->send();

		return $response;
	}

	public function refund(Payment $payment, PayableInterface $refund)
	{
		$response = $this->_server->refund([
			'amount'        => $refund->amount,
			'currency'      => $refund->currency,
			'description'   => 'Refund payment ' . $payment->id,
			'transactionId' => ...,
		])->send();

		return $response;
	}
}