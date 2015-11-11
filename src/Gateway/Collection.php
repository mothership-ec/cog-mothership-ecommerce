<?php

namespace Message\Mothership\Ecommerce\Gateway;

use Message\Cog\ValueObject\Collection as BaseCollection;

/**
 * Temporary class to be removed once collections have been refactored.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 * @author Thomas Marchant <thomas@mothership.ec>
 */
class Collection extends BaseCollection
{
	/**
	 * {@inheritDoc}
	 */
	protected function _configure()
	{
		$this->addValidator(function ($item) {
			if (!$item instanceof GatewayInterface) {
				$type = gettype($item) === 'object' ? get_class($item) : gettype($item);
				throw new \InvalidArgumentException('Items added to Gateway\\Collection must be instances of Gateway\\GatewayInterface, ' . $type . ' given');
			}
		});

		$this->setKey(function ($item) {
			return $item->getName();
		});
	}
}