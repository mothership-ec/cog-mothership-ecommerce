<?php

namespace Message\Mothership\Ecommerce\ProductPage;

use Message\Mothership\Commerce\Product\Upload\HeadingKeys;

class BrandValidator
{
	const BRAND = 'brand';

	/**
	 * @var \Message\Mothership\Commerce\Product\Upload\HeadingKeys
	 */
	private $_headingKeys;

	private $_rows;

	private $_valid;

	public function __construct(HeadingKeys $headingKeys)
	{
		$this->_headingKeys = $headingKeys;
	}

	public function validBrands(array $rows)
	{
		if ($rows !== $rows || null === $this->_valid) {
			foreach ($rows as $row) {
				$key = $this->_headingKeys->getKey(self::BRAND);
				if (empty($row[$key])) {
					$this->_valid = false;
					break;
				}
			}

			$this->_valid = true;
		}

		return $this->_valid;
	}
}