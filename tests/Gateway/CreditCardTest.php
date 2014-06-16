<?php

namespace Message\Mothership\Ecommerce\Test\Gateway;

use Mockery as m;
use PHPUnit_Framework_TestCase;

/**
 * Covers Message\Mothership\Ecommerce\Gateway\CreditCard
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class CreditCardTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_address = m::mock('\Message\Mothership\Commerce\Address\Address');
		$this->_address->forename  = "test";
		$this->_address->surname   = "test";
		$this->_address->lines     = [1 => "test", 2 => "test"];
		$this->_address->town      = "test";
		$this->_address->postcode  = "test";
		$this->_address->stateID   = "test";
		$this->_address->countryID = "test";

		// :(
		$this->_card = m::mock('\Message\Mothership\Ecommerce\Gateway\CreditCard[setShippingFirstName,setShippingLastName,setShippingAddress1,setShippingAddress2,setShippingCity,setShippingPostcode,setShippingState,setShippingCountry,setFirstName,setLastName,setAddress1,setAddress2,setCity,setPostcode,setState,setCountry]');
	}

	public function tearDown()
	{
		m::close();
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\CreditCard::setDeliveryAddress
	 */
	public function testSetDeliveryAddress()
	{
		$this->_card
			->shouldReceive('setShippingFirstName')->once()
			->shouldReceive('setShippingLastName')->once()
			->shouldReceive('setShippingAddress1')->once()
			->shouldReceive('setShippingAddress2')->once()
			->shouldReceive('setShippingCity')->once()
			->shouldReceive('setShippingPostcode')->once()
			->shouldReceive('setShippingState')->once()
			->shouldReceive('setShippingCountry')->once();

		$this->_card->setDeliveryAddress($this->_address);
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\CreditCard::setBillingAddress
	 */
	public function testSetBillingAddress()
	{
		$this->_card
			->shouldReceive('setFirstName')->once()
			->shouldReceive('setLastName')->once()
			->shouldReceive('setAddress1')->once()
			->shouldReceive('setAddress2')->once()
			->shouldReceive('setCity')->once()
			->shouldReceive('setPostcode')->once()
			->shouldReceive('setState')->once()
			->shouldReceive('setCountry')->once();

		$this->_card->setBillingAddress($this->_address);
	}
}