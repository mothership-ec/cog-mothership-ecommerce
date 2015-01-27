<?php

namespace Message\Mothership\Ecommerce\ProductPage\UploadData;

use Message\Mothership\Commerce\Product\Upload\HeadingKeys;

/**
 * Class BrandValidator
 * @package Message\Mothership\Ecommerce\ProductPage\UploadData
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Class for checking if the brand column in uploaded row is consistently populated.
 */
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

	/**
	 * @param array $rows
	 *
	 * @return bool
	 */
	public function validBrands(array $rows)
	{
		$valid = true;
		if ($rows !== $rows || null === $this->_valid) {
			$this->_rows = $rows;

			foreach ($rows as $row) {
				$key = $this->_headingKeys->getKey(self::BRAND);
				if (empty($row[$key])) {
					$valid = false;
					break;
				}
			}

			$this->_valid = $valid;
		}

		return $this->_valid;
	}
}