<?php

namespace Message\Mothership\Ecommerce\Test\Gateway\ZeroPayment;

use PHPUnit_Framework_TestCase;
use Message\Mothership\Ecommerce\Gateway\ZeroPayment\Gateway;

/**
 * Covers Message\Mothership\Ecommerce\Gateway\ZeroPayment\Gateway
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class GatewayTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_method = new Gateway;
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\ZeroPayment\Gateway::getName
	 */
	public function testName()
	{
		$this->assertEquals(
			'zero-payment',
			$this->_method->getName()
		);
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\ZeroPayment\Gateway::getPurchaseControllerReference
	 */
	public function testPurchaseControllerReference()
	{
		$this->assertEquals(
			'Message:Mothership:Ecommerce::Controller:Gateway:ZeroPayment#purchase',
			$this->_method->getPurchaseControllerReference()
		);
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\ZeroPayment\Gateway::getRefundControllerReference
	 */
	public function testRefundControllerReference()
	{
		$this->assertEquals(
			'Message:Mothership:Ecommerce::Controller:Gateway:ZeroPayment#refund',
			$this->_method->getRefundControllerReference()
		);
	}
}