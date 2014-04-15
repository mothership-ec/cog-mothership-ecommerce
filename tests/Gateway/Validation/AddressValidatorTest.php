<?php

namespace Message\Mothership\Ecommerce\Test\Gateway\Validation;

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Message\Mothership\Ecommerce\Gateway\Validation\AddressValidator;

/**
 * Covers Message\Mothership\Ecommerce\Gateway\Validation\AddressValidator
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class AddressValidatorTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_payable = m::mock('\Message\Mothership\Commerce\Payable\PayableInterface');
	}

	public function tearDown()
	{
		m::close();
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\Validation\AddressValidator::isValid
	 */
	public function testValidAddress()
	{
		$type    = "delivery";
		$parts   = ["lines" => [1,2], "town", "postcode"];
		$address = $this->_getAddress($parts);

		$validator = new AddressValidator($type, $parts);

		$this->_payable
			->shouldReceive('getPayableAddress')
			->once()
			->with($type)
			->andReturn($address);

		$valid = $validator->isValid($this->_payable);

		$this->assertSame(true, $valid);
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\Validation\AddressValidator::isValid
	 */
	public function testMissingAddress()
	{
		$type    = "delivery";
		$parts   = ["postcode"];

		$validator = new AddressValidator($type, $parts);

		$this->_payable
			->shouldReceive('getPayableAddress')
			->once()
			->with($type)
			->andReturn(null);

		$valid  = $validator->isValid($this->_payable);
		$errors = $validator->getErrors();

		$this->assertSame(false, $valid);
		$this->assertInternalType('array', $errors);
		$this->assertSame(1, count($errors));
		$this->assertSame("Delivery address is required", array_shift($errors));
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\Validation\AddressValidator::isValid
	 */
	public function testAddressWithMissingRequiredPart()
	{
		$type    = "delivery";
		$parts   = $required = ["town"];
		$address = $this->_getAddress($parts);

		// Add additional required field that has not been populated
		$required[] = "postcode";

		$validator = new AddressValidator($type, $required);

		$this->_payable
			->shouldReceive('getPayableAddress')
			->once()
			->with($type)
			->andReturn($address);

		$valid = $validator->isValid($this->_payable);
		$errors = $validator->getErrors();

		$this->assertSame(false, $valid);
		$this->assertInternalType('array', $errors);
		$this->assertSame(1, count($errors));
		$this->assertSame("Delivery address postcode is required", array_shift($errors));
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\Validation\AddressValidator::isValid
	 */
	public function testAddressWithMultipleMissingRequiredParts()
	{
		$type    = "delivery";
		$parts   = $required = [];
		$address = $this->_getAddress($parts);

		// Add additional required field that has not been populated
		$required[] = "town";
		$required[] = "postcode";

		$validator = new AddressValidator($type, $required);

		$this->_payable
			->shouldReceive('getPayableAddress')
			->once()
			->with($type)
			->andReturn($address);

		$valid = $validator->isValid($this->_payable);
		$errors = $validator->getErrors();

		$this->assertSame(false, $valid);
		$this->assertInternalType('array', $errors);
		$this->assertSame(2, count($errors));
		$this->assertSame("Delivery address town is required", array_shift($errors));
		$this->assertSame("Delivery address postcode is required", array_shift($errors));
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\Validation\AddressValidator::isValid
	 */
	public function testAddressWithMissingLine()
	{
		$type    = "delivery";
		$parts   = $required = ["lines" => [1], "town", "postcode"];
		$address = $this->_getAddress($parts);

		// Add additional required line that has not been populated
		$required["lines"] = [1,2];

		$validator = new AddressValidator($type, $required);

		$this->_payable
			->shouldReceive('getPayableAddress')
			->once()
			->with($type)
			->andReturn($address);

		$valid  = $validator->isValid($this->_payable);
		$errors = $validator->getErrors();

		$this->assertSame(false, $valid);
		$this->assertInternalType('array', $errors);
		$this->assertSame(1, count($errors));
		$this->assertSame("Delivery address line 2 is required", array_shift($errors));
	}

	/**
	 * @covers \Message\Mothership\Ecommerce\Gateway\Validation\AddressValidator::isValid
	 */
	public function testAddressWithMultipleMissingLines()
	{
		$type    = "delivery";
		$parts   = $required = ["lines" => [1], "town", "postcode"];
		$address = $this->_getAddress($parts);

		// Add additional required line that has not been populated
		$required["lines"] = [1,2,3];

		$validator = new AddressValidator($type, $required);

		$this->_payable
			->shouldReceive('getPayableAddress')
			->once()
			->with($type)
			->andReturn($address);

		$valid  = $validator->isValid($this->_payable);
		$errors = $validator->getErrors();

		$this->assertSame(false, $valid);
		$this->assertInternalType('array', $errors);
		$this->assertSame(2, count($errors));
		$this->assertSame("Delivery address line 2 is required", array_shift($errors));
		$this->assertSame("Delivery address line 3 is required", array_shift($errors));
	}

	/**
	 * Build an address with values for the given parts.
	 *
	 * @param  array  $parts
	 * @return Mock address
	 */
	protected function _getAddress(array $parts)
	{
		$address = m::mock('\Message\Mothership\Commerce\Address\Address');

		foreach ($parts as $key => $part) {
			if (is_array($part)) {
				$address->$key = [];
				foreach ($part as $keyInner) {
					$address->{$key}[$keyInner] = "test";
				}
			} else {
				$address->$part = "test";
			}
		}

		return $address;
	}
}