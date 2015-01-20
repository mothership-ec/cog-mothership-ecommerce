<?php

namespace Message\Mothership\Ecommerce\ProductPage\UploadData;

use Message\Mothership\Commerce\Product\Upload\HeadingKeys;

/**
 * Class VariantNameCrawler
 * @package Message\Mothership\Ecommerce\ProductPage\UploadData
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Class for collecting unique variant names from uploaded product data
 */
class VariantNameCrawler
{
	/**
	 * @var HeadingKeys
	 */
	private $_headingKeys;

	/**
	 * @var array
	 */
	private $_variantNames = [];

	/**
	 * @var array
	 */
	private $_variantKeys = [];

	public function __construct(HeadingKeys $headingKeys)
	{
		$this->_headingKeys = $headingKeys;
		$this->_setVariantKeys();
	}

	/**
	 * Get an array of unique variant names
	 *
	 * @param array $rows
	 *
	 * @return array
	 */
	public function getVariantNames(array $rows = null)
	{
		if ($rows && empty($this->_variantNames)) {
			$this->_setVariantNames($rows);
		}

		return $this->_variantNames;
	}

	/**
	 * Loop through rows and identify unique variant names
	 *
	 * @param array $rows
	 */
	private function _setVariantNames(array $rows)
	{
		foreach ($this->_variantKeys as $variantKey) {
			$variantKeyTrans = $this->_headingKeys->getKey($variantKey);

			if (!array_key_exists($variantKey, $this->_variantNames)) {
				$this->_variantNames[$variantKey] = [];
			}

			foreach ($rows as $product) {
				foreach ($product as $row) {
					$this->_validateRow($row, $variantKeyTrans);

					$variantName = $row[$variantKeyTrans];

					$variantName = ucfirst(strtolower($variantName));

					if (!empty($variantName) && !in_array($variantName, $this->_variantNames[$variantKey])) {
						$this->_variantNames[$variantKey][] = $variantName;
					}
				}
			}

			$this->_variantNames[$variantKey] = implode(', ', $this->_variantNames[$variantKey]);
		}
	}

	/**
	 * @param array $row
	 * @param $variantKey
	 * @throws \LogicException
	 */
	private function _validateRow(array $row, $variantKey)
	{
		if (!array_key_exists($variantKey, $row)) {
			throw new \LogicException('Array key `' . $variantKey . '` does not exist in row');
		}
	}

	/**
	 * Build array of keys for variants depending on how many variants are specified to be in the product data
	 */
	private function _setVariantKeys()
	{
		for ($i = 1; $i <= HeadingKeys::NUM_VARIANTS; ++$i) {
			$this->_variantKeys[] = HeadingKeys::VAR_NAME_PREFIX . $i;
		}
	}
}