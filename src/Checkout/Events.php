<?php

namespace Message\Mothership\Ecommerce\Checkout;

/**
 * Class Events
 * @package Message\Mothership\Ecommerce\Checkout
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class of constants for identifying events dispatched during checkout
 */
class Events
{
	// Fired at initial stage of checkout if any units are changed
	const REVIEW = 'ecom.checkout.review';

	// Fired after the user has submitted their address details
	const ADDRESSES = 'ecom.checkout.address';

	// Fired before the payment stages after the user has submitted any notes and confirmed their order is correct
	const CONFIRM = 'ecom.checkout.confirm';
}