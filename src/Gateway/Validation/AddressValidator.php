<?php

namespace Message\Mothership\Ecommerce\Gateway\Validation;

use Message\Mothership\Commerce\Payable\PayableInterface;

/**
 * Validate an address on a payable has all the required components.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class AddressValidator implements ValidatorInterface
{
	/**
	 * The address type such as 'billing' or 'delivery'.
	 *
	 * @var string
	 */
	protected $_type;

	/**
	 * List of errors created by an invalid payable.
	 *
	 * @var array
	 */
	protected $_errors;

	/**
	 * Construct the validator with the address type.
	 *
	 * @param string $type
	 */
	public function __construct($type, array $parts)
	{
		$this->_type  = $type;
		$this->_parts = $parts;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isValid(PayableInterface $payable)
	{
		$valid = true;

		$address = $payable->getPayableAddress($this->_type);

		if (! $address) {
			$this->_errors[] = sprintf("%s address is required", ucfirst($this->_type));

			return false;
		}

		foreach ($this->_parts as $key => $value) {
			if ($key === "lines") {
				for ($i = 1; $i <= $value; $i++) {
					if (! property_exists($address, "lines") or ! isset($address->lines[$i])) {
						$valid = false;
						$this->_errors[] = sprintf("%s address line %i is required", ucfirst($this->_type), $value);
					}
				}
			}
			else {
				if (! property_exists($address, $part) or ! $address->$part) {
					$valid = false;
					$this->_errors[] = sprintf("%s address %s is required", ucfirst($this->_type), $part);
				}
			}
		}

		// $states = $this->get('state.list')->all();
		// if (isset($states[$address->countryID]) and
		// 	(empty($address->stateID) or ! isset($states[$address->countryID][$address->stateID]))
		// ) {
		// 	$addressIncomplete = true;
		// }

		return $valid;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
}