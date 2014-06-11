<?php

namespace Message\Mothership\Ecommerce\Gateway;

/**
 * Temporary class to be removed once collections have been refactored.
 *
 * @todo   Remove this and replace instances of it with the cog collection
 *         class once implemented.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Collection implements \IteratorAggregate, \Countable
{
	protected $_items = array();

	public function __construct(array $items = array())
	{
		foreach ($items as $item) {
			$this->add($item);
		}
	}

	public function add(GatewayInterface $item)
	{
		if ($this->exists($item->getName())) {
			throw new \InvalidArgumentException(sprintf(
				'Gateway `%s` is already defined',
				$item->getName()
			));
		}

		$this->_items[$item->getName()] = $item;

		return $this;
	}

	public function get($name)
	{
		if (!$this->exists($name)) {
			throw new \InvalidArgumentException(sprintf('Gateway `%s` not set on collection', $name));
		}

		return $this->_items[$name];
	}

	public function all()
	{
		return $this->_items;
	}

	public function exists($name)
	{
		return array_key_exists($name, $this->_items);
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