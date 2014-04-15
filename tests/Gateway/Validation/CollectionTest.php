<?php

namespace Message\Mothership\Ecommerce\Test\Gateway\Validation;

use Mockery as m;
use PHPUnit_Framework_TestCase;

class CollectionTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_payable   = m::mock('\Message\Mothership\Commerce\Payable\PayableInterface');
		$this->_validator = m::mock('\Message\Mothership\Ecommerce\Gateway\Validation\Collection[all]');
	}

	public function tearDown()
	{
		m::close();
	}

	public function testValidPayable()
	{
		$ruleOne = $ruleTwo = m::mock();

		$rules = [$ruleOne, $ruleTwo];

		$this->_validator
			->shouldReceive('all')
			->once()
			->andReturn($rules);

		$ruleOne
			->shouldReceive('isValid')
			->once()
			->with($this->_payable)
			->andReturn(true);

		$ruleTwo
			->shouldReceive('isValid')
			->once()
			->with($this->_payable)
			->andReturn(true);

		$this->_validator->isValid($this->_payable);
	}

	public function testInvalidPayableFailingMultipleRules()
	{
		$ruleOne = $ruleTwo = m::mock();

		$rules = [$ruleOne, $ruleTwo];

		$this->_validator
			->shouldReceive('all')
			->once()
			->andReturn($rules);

		$ruleOne
			->shouldReceive('isValid')
			->once()
			->with($this->_payable)
			->andReturn(false);

		$ruleOne
			->shouldReceive('getErrors')
			->once()
			->andReturn(['A test error one']);

		$ruleTwo
			->shouldReceive('isValid')
			->once()
			->with($this->_payable)
			->andReturn(false);

		$ruleTwo
			->shouldReceive('getErrors')
			->once()
			->andReturn(['A test error two']);

		$this->_validator->isValid($this->_payable);
	}

	public function testInvalidPayableFailingOneOfMultipleRules()
	{
		$ruleOne = $ruleTwo = m::mock();

		$rules = [$ruleOne, $ruleTwo];

		$this->_validator
			->shouldReceive('all')
			->once()
			->andReturn($rules);

		$ruleOne
			->shouldReceive('isValid')
			->once()
			->with($this->_payable)
			->andReturn(false);

		$ruleOne
			->shouldReceive('getErrors')
			->once()
			->andReturn(['A test error one']);

		$ruleTwo
			->shouldReceive('isValid')
			->once()
			->with($this->_payable)
			->andReturn(true);

		$this->_validator->isValid($this->_payable);
	}
}