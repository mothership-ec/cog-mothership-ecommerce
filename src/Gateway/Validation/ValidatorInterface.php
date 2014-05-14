<?php

namespace Message\Mothership\Ecommerce\Gateway\Validation;

use Message\Mothership\Commerce\Payable\PayableInterface;

/**
 * Interface for gateway validators for checking a payable is valid.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
interface ValidatorInterface
{
	/**
	 * Check if a payable is valid.
	 *
	 * @param  PayableInterface $payable
	 * @return boolean
	 */
	public function isValid(PayableInterface $payable);

	/**
	 * Get the list of errors that were created by an invalid payable.
	 *
	 * @return array
	 */
	public function getErrors();
}