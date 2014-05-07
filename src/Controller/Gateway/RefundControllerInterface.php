<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

use Message\Mothership\Commerce\Payable\PayableInterface;

/**
 * Interface for gateway refund controllers.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
interface RefundControllerInterface
{
	/**
	 * Refund a payable.
	 *
	 * @param  PayableInterface $payable
	 * @param  string           $reference
	 * @param  array            $stages  Routes for redirecting the customer
	 * @param  array            $options
	 * @return \Message\Cog\HTTP\Response
	 */
	public function refund(PayableInterface $payable, $reference, array $stages, array $options = null);
}