<?php

namespace Message\Mothership\Ecommerce\Gateway\Validation;

use Message\Mothership\Commerce\...\PayableInterface;
use Message\Cog\Collection\Collection as BaseCollection;

/**
 * Collection for a gateway's validation rules for payables.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Collection extends BaseCollection implements ValidatorInterface
{
	/**
	 * List of errors created by an invalid payable.
	 *
	 * @var array
	 */
	protected $_errors = [];

	/**
	 * {@inheritDoc}
	 */
	public function isValid(PayableInterface $payable)
	{
		$valid = true;

		foreach ($this->all() as $validator)
		{
			$valid = ($valid and $validator->isValid($payable));

			$this->_errors = array_merge(
				$this->_errors,
				$validator->getErrors()
			);
		}

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