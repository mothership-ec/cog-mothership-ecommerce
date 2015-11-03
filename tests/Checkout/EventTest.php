<?php

namespace Message\Mothership\Ecommerce\Test\Checkout;

use Message\Mothership\Ecommerce\Checkout\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
	public function testGetOrder()
	{
		$order = $this->_getOrder();
		$event = new Event($order);

		$this->assertSame($order, $event->getOrder());
	}

	public function testGetData()
	{
		$data = ['hello' => 'world'];
		$event = new Event($this->_getOrder(), $data);

		$this->assertSame($data, $event->getData());
	}

	public function testGetDataEmpty()
	{
		$event = new Event($this->_getOrder());

		$this->assertSame([], $event->getData());
	}

	public function testSetOrder()
	{
		$event = new Event($this->_getOrder());
		$order = $this->_getOrder();
		$order->id = 1;
		$event->setOrder($order);

		$this->assertSame($order, $event->getOrder());
	}

	public function testSetData()
	{
		$event = new Event($this->_getOrder(), ['hello' =>  'world']);
		$data = ['goodbye' => 'moon'];
		$event->setData($data);

		$this->assertSame($data, $event->getData());
	}

	public function testSetDataFromEmpty()
	{
		$event = new Event($this->_getOrder());
		$data = ['goodbye' => 'moon'];
		$event->setData($data);

		$this->assertSame($data, $event->getData());
	}

	private function _getOrder()
	{
		return $this->getMockBuilder('Message\\Mothership\\Commerce\\Order\\Order')
			->disableOriginalConstructor()
			->getMock()
		;
	}
}