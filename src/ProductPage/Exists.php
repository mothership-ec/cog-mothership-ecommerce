<?php

namespace Message\Mothership\Ecommerce\ProductPage;

use Message\Cog\DB\Query;
use Message\Mothership\Commerce\Product\Product;

class Exists
{
	private $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function exists(Product $product, $variantName = null, $variantValue = null)
	{
		if ((null !== $variantName) xor (null !==$variantValue)) {
			throw new \LogicException('Cannot have either a variant name or a value, must have neither or both');
		}

		$result = $this->_query->run("
			SELECT
				page_id
			FROM
				page
				" . (null !== $variantName ? "
				JOIN
					(
						SELECT
							page_id,
							value_string AS variantName
						FROM
							page_content
						WHERE
							data_name = :name?s
						AND
							value_string = :variantName?s
					) AS variantName
				USING
					(page_id)
				JOIN
					(
						SELECT
							page_id,
							value_string AS variantValue
						FROM
							page_content
						WHERE
							data_name = :value?s
						AND
							value_string = :variantValue?s
					) AS variantValue
				USING
					(page_id)
				" : "") . "
			JOIN
				page_content AS pc
			USING
				(page_id)
			WHERE
				page.type = :pageType?s
			AND
				pc.group_name = :product?s
			AND
				(pc.field_name = :product?s AND pc.value_int = :productID?s)
		", [
				'name'         => 'name',
				'variantName'  => $variantName,
				'value'        => 'value',
				'variantValue' => $variantValue,
				'pageType'     => 'product',
				'product'      => 'product',
				'productID'    => $product->id,
			]
		)->flatten();

		return count($result) > 0;
	}
}