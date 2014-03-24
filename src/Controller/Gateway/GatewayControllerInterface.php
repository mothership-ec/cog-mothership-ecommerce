<?php

namespace Message\Mothership\Ecommerce\Gateway;

use Message\Mothership\Commerce\...\PayableInterface;

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
	 * Refund a payment against a refund payable. The refund amount does not
	 * have to equal the original payment amount.
	 *
	 * @param  PayableInterface           $payment
	 * @param  PayableInterface           $refund
	 * @return \Message\Cog\HTTP\Response
	 */
	public function refund(PayableInterface $payment, PayableInterface $refund);
}