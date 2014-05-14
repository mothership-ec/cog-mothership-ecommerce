<?php

namespace Message\Mothership\Ecommerce\Test\Gateway\LocalPayment;

use PHPUnit_Framework_TestCase;
use Message\Mothership\Ecommerce\Gateway\LocalPayment\Gateway;

/**
 * Covers Message\Mothership\Ecommerce\Gateway\LocalPayment\Gateway
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
	 * @covers \Message\Mothership\Ecommerce\Gateway\LocalPayment\Gateway::getName
	 */
	public function testName()
	{
		$this->assertEquals(
			'local-payment',
			$this->_method->getName()
		);
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\LocalPayment\Gateway::getPurchaseControllerReference
	 */
	public function testPurchaseControllerReference()
	{
		$this->assertEquals(
			'Message:Mothership:Ecommerce::Controller:Gateway:LocalPayment#purchase',
			$this->_method->getPurchaseControllerReference()
		);
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\LocalPayment\Gateway::getRefundControllerReference
	 */
	public function testRefundControllerReference()
	{
		$this->assertEquals(
			'Message:Mothership:Ecommerce::Controller:Gateway:LocalPayment#refund',
			$this->_method->getRefundControllerReference()
		);
	}
}