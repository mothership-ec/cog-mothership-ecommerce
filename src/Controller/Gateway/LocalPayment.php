<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

/**
 * Local payment gateway controller.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class LocalPayment extends ZeroPayment
{
	const REFERENCE_PREFIX = "local-payment-";
}