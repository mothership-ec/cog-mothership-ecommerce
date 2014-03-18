<?php

namespace Message\Mothership\Ecommerce\Gateway;

/**
 * Interface for payment gateways.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
interface GatewayInteface
{
	/**
	 * The gateway's identifier.
	 *
	 * @return string
	 */
	public function getName();
}