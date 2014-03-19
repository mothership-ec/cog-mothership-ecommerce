<?php

namespace Message\Mothership\Ecommerce\Test\Gateway\SagePay;

use PHPUnit_Framework_TestCase;
use Message\Mothership\Ecommerce\Gateway\SagePay\Gateway;

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

	public function testRefundPayable()
	{

	}

	public function testRefundFreePayable()
	{

	}
}