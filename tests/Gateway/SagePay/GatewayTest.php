<?php

namespace Message\Mothership\Ecommerce\Test\Gateway\SagePay;

use PHPUnit_Framework_TestCase;

/**
 * Covers \Message\Mothership\Ecommerce\Gateway\SagePay\Gateway
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class GatewayTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_server = $this->getMock('\\Omnipay\\SagePay\\ServerGateway');
		$this->_cache  = $this->getMock('\\Message\\Cog\\Cache\\CacheInterface');

		$this->_gateway = $this->getMock(
			'\\Message\\Mothership\\Ecommerce\\Gateway\\SagePay\\Gateway',
			[],
			[$this->_server, $this->_cache]
		);
	}

	/**
	 * Covers purchase with successful response.
	 */
	public function testPurchasePayable()
	{
		$payable = $this->getMock('\\Message\\Mothership\\Commerce\\PayableInterface');
		$card    = $this->getMock('\\OmniPay\\Common\\CreditCard');

		$request  = $this->getMock('\\Omnipay\\SagePay\\Message\\ServerPurchaseRequest');
		$response = $this->getMock('\\Omnipay\\SagePay\\Message\\Response');

		$this->_server
			->expects($this->once())
			->method('purchase')
			->will($this->returnValue($request));

		$request
			->expects($this->once())
			->method('send')
			->will($this->returnValue($response));

		$response
			->expects($this->once())
			->method('isRedirect')
			->will($this->returnValue(false));

		$this->_gateway->purchase($payable, $card);
	}

	/**
	 * Covers purchase with redirect response and stores data in cache.
	 */
	public function testPurchaseStoresCacheOnRedirect()
	{
		$payable = $this->getMock('\\Message\\Mothership\\Commerce\\PayableInterface');
		$card    = $this->getMock('\\OmniPay\\Common\\CreditCard');

		$request  = $this->getMock('\\Omnipay\\SagePay\\Message\\ServerPurchaseRequest');
		$response = $this->getMock('\\Omnipay\\SagePay\\Message\\Response');

		$this->_server
			->expects($this->once())
			->method('purchase')
			->will($this->returnValue($request));

		$request
			->expects($this->once())
			->method('send')
			->will($this->returnValue($response));

		$response
			->expects($this->once())
			->method('isRedirect')
			->will($this->returnValue(true));

		$this->_cache
			->expects($this->once())
			->method('store');

		$this->_gateway->purchase($payable, $card);
	}

	/**
	 * Covers completing a purchase with a valid transaction id.
	 */
	public function testCompletePurchase()
	{
		$transactionID = 'test-id';
		$path = 'something' . $transactionID;

		$data = [
			'foo' => 'bar'
		];

		$this->_cache
			->expects($this->once())
			->method('exists')
			->with($path)
			->will($this->returnValue(true));

		$this->_cache
			->expects($this->once())
			->method('fetch')
			->with($path)
			->will($this->returnValue($data));

		$this->_cache
			->expects($this->once())
			->method('delete')
			->with($path);

		$this->_server
			->expects($this->once())
			->method('completePurchase')
			->will($this->returnValue($request));

		$request
			->expects($this->once())
			->method('send')
			->will($this->returnValue($response));

		$this->_gateway->completePurchase($transactionID);
	}

	/**
	 * Covers completing a purchase with an invalid transaction id.
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function testCompletePurchaseWithInvalidTransactionIDThrowsException()
	{
		$transactionID = 'test-id';
		$path = 'something' . $transactionID;

		$this->_cache
			->expects($this->once())
			->method('exists')
			->with($path)
			->will($this->returnValue(false));
	}

	public function testRefundPayable()
	{

	}

	public function testRefundFreePayable()
	{

	}
}