<?php

namespace Message\Mothership\Ecommerce\Gateway\Validation;

// use Message\Cog\Collection\Collection as BaseCollection;
use Message\Mothership\Commerce\Payable\PayableInterface;

/**
 * Collection for a gateway's validation rules for payables.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Collection implements ValidatorInterface, \IteratorAggregate, \Countable
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

		foreach ($this->all() as $validator) {
			$innerValid = $validator->isValid($payable);
			$valid = ($valid and $innerValid);

			if (! $innerValid) {
				$this->_errors = array_merge(
					$this->_errors,
					$validator->getErrors()
				);
			}
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





	/**
	 * Temporary stuff to be removed once collections have been refactored.
	 */

	protected $_items = array();

	public function __construct(array $items = array())
	{
		foreach ($items as $item) {
			$this->add($item);
		}
	}

	public function add(ValidatorInterface $item)
	{
		$this->_items[] = $item;

		return $this;
	}

	public function all()
	{
		return $this->_items;
	}

	public function count()
	{
		return count($this->_items);
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_items);
	}
}