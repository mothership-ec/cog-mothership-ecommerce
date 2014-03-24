<?php

namespace Message\Mothership\Ecommerce\Gateway\Sagepay;

use Monolog\Logger;
use InvalidArgumentException;
use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayFactory;
use Omnipay\SagePay\ServerGateway;
use Message\Cog\Cache\CacheInterface;
use Message\Mothership\Commerce\...\PayableInterface;
use Omnipay\SagePay\Message\Response as SagePayResponse;
use Message\Mothership\Ecommerce\Gateway\GatewayInterface;

/**
 * SagePay payment gateway that integrates with the SagePay Server api via an
 * OmniPay interface. Provides methods for purchases and refunds.
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
	 * Logger for notifying developers of responses.
	 *
	 * @var Logger
	 */
	protected $_logger;

	/**
	 * Constructor.
	 *
	 * @param ServerGateway  $server
	 * @param CacheInterface $cache
	 * @param Logger         $logger
	 */
	public function __construct(
		ServerGateway $server,
		CacheInterface $cache,
		Logger $logger
	) {
		$this->_server = $server;
		$this->_cache  = $cache;
		$this->_logger = $logger;
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
	 * @param  string           $returnUrl
	 * @return SagePayResponse
	 */
	public function purchase(PayableInterface $payable, $returnUrl)
	{
		$card = new CreditCard;
		$card->setDeliveryAddress($payable->getAddress('delivery'))
		     ->setBillingAddress($payable->getAddress('billing'));

		$response = $this->_server->purchase([
			'amount'    => $payable->getAmount(),
			'currency'  => $payable->getCurrency(),
			'card'      => $card,
			'returnUrl' => $returnUrl,
		])->send();

		$this->logResponse($response);

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
	 * from the cache against the given transaction ID.
	 *
	 * @param  string $transactionID
	 * @return SagePayResponse
	 */
	public function completePurchase($transactionID)
	{
		$path = self::CACHE_PREFIX . $transactionID;

		if (! $this->_cache->exists($path)) {
			throw new InvalidArgumentException(sprintf(
				"Stored cache of transaction '%s' could not be found at '%s'",
				$transactionID,
				$path
			));
		}

		$data = $this->_cache->fetch($path);
		$this->_cache->delete($path);

		$response = $this->_server->completePurchase([
			'transactionId'        => $transactionId,
			'transactionReference' => json_encode($data)
		])->send();

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Attempt to refund a transaction with a payable. The refund is linked to
	 * the original transaction but does not have to be a refund for the
	 * full payment amount.
	 *
	 * @param  string           $transactionID
	 * @param  PayableInterface $refund
	 * @return SagePayResponse
	 */
	public function refund($transactionID, PayableInterface $refund)
	{
		$response = $this->_server->refund([
			'amount'        => $refund->amount,
			'currency'      => $refund->currency,
			'description'   => 'Refund transaction ' . $transactionID,
			'transactionId' => $transactionID,
		])->send();

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Log a response.
	 *
	 * @param  SagePayResponse $response
	 */
	public function logResponse(SagePayResponse $response)
	{
		$data = $response->getData();

		switch($data['Status']) {
			case 'OK':
				$this->_logger->info(
					"A connection to the payment gateway was made successfully.",
					$data
				);
				break;

			case 'OK REPEATED':
				$this->_logger->notice(
					"A connection to the payment gateway was repeated.",
					$data
				);
				break;

			case 'INVALID':
				$this->_logger->warning(
					"Some data sent to the payment gateway was invalid.",
					$data
				);
				break;

			case 'MALFORMED':
				$this->_logger->alert(
					"The data sent to the payment gateway was malformed.",
					$data
				);
				break;

			case 'ERROR':
				$this->_logger->alert(
					"An error occurred at the payment gateway.",
					$data
				);
				break;
		}
	}
}