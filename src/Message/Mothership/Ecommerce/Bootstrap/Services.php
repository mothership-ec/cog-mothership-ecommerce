<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Mothership\Ecommerce\OrderItemStatuses;

use Message\Mothership\Commerce\Order\Status\Status;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$this->addOrderStatuses($services);

		$services['form.orders.checkbox'] = function($sm) {
			return new \Message\Mothership\Ecommerce\Form\Orders($sm);
		};
	}

	public function addOrderStatuses($services)
	{
		$services['order.statuses']
			->add(new Status(OrderItemStatuses::RETURNED, 'Fully returned'));

		$services['order.item.statuses']
			->add(new Status(OrderItemStatuses::AWAITING_PAYMENT, 'Awaiting Payment'))
			->add(new Status(OrderItemStatuses::HOLD,             'On Hold'))
			->add(new Status(OrderItemStatuses::PRINTED,          'Printed'))
			->add(new Status(OrderItemStatuses::PICKED,           'Picked'))
			->add(new Status(OrderItemStatuses::PACKED,           'Packed'))
			->add(new Status(OrderItemStatuses::POSTAGED,         'Postaged'))
			->add(new Status(OrderItemStatuses::RETURN_WAITING,   'Waiting to Receive Returned Item'))
			->add(new Status(OrderItemStatuses::RETURN_ARRIVED,   'Returned Item Arrived'))
			->add(new Status(OrderItemStatuses::RETURNED,         'Returned'));
	}
}