<?php

namespace Message\Mothership\Ecommerce\Gateway;

use Message\Mothership\Commerce\Payable\PayableInterface;

/**
 * Interface for gateway refund controllers.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
interface RefundControllerInterface
{
	/**
	 * Refund a payment.
	 *
	 * @param  PayableInterface           $refund
	 * @param  array                      $options
	 * @return \Message\Cog\HTTP\Response
	 */
	public function refund(PayableInterface $refund, array $options = null);
}