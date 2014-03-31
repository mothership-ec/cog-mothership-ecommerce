<?php

namespace Message\Mothership\Ecommerce\Gateway;

/**
 * Interface for payment gateways.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
interface GatewayInterface
{
	/**
	 * The gateway's identifier.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * The reference for the controller method that handles the purchase.
	 *
	 * @return string
	 */
	public function getPurchaseControllerReference();

	/**
	 * The reference for the controller method that handles the refund.
	 *
	 * @return string
	 */
	public function getRefundControllerReference();
}