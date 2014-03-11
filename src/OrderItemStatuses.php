<?php

namespace Message\Mothership\Ecommerce;

use Message\Mothership\Commerce\Order\Statuses;

/**
 * Container for status codes made available to order items by the Ecommerce
 * cogule.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class OrderItemStatuses
{
	const CANCELLED        = -400;
	const AWAITING_PAYMENT = -200;
	const HOLD             = -100;

	const PRINTED          = 100;
	const PICKED           = 200;
	const PACKED           = 300;
	const POSTAGED         = 400;

	const DISPATCHED       = 1000;
	const RETURN_WAITING   = 1200;
	const RETURN_ARRIVED   = 1300;
	const RETURNED         = 1500;
}