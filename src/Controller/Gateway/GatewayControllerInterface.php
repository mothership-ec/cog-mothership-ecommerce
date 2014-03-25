<?php

namespace Message\Mothership\Ecommerce\Gateway;

use Message\Mothership\Commerce\Payable\PayableInterface;

/**
 * Interface for gateway controllers.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
interface GatewayControllerInterface
{
	/**
	 * Purchase a payable.
	 *
	 * @param  PayableInterface           $payable
	 * @param  array                      $options
	 * @return \Message\Cog\HTTP\Response
	 */
	public function purchase(PayableInterface $payable, array $options = null);

	/**
	 * Refund a payment.
	 *
	 * @param  PayableInterface           $refund
	 * @param  array                      $options
	 * @return \Message\Cog\HTTP\Response
	 */
	public function refund(PayableInterface $refund, array $options = null);
}