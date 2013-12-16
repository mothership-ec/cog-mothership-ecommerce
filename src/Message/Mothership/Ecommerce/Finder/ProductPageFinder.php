<?php

namespace Message\Mothership\Ecommerce\Finder;

use Message\Cog\DB;
use Message\Mothership\CMS\Page;
use Message\Mothership\Commerce\Product\Product;

class ProductPageFinder
{
	protected $_query;
	protected $_loader;
	protected $_auth;

	protected $_filters = array();

	public function __construct(DB\Query $query, Page\Loader $loader, Page\Authorisation $auth)
	{
		$this->_query  = $query;
		$this->_loader = $loader;
		$this->_auth   = $auth;
	}

	public function addFilter($callable)
	{
		if (!is_callable($callable)) {
			throw new \InvalidArgumentException('Filters for ProductPageFinder must be callable.');
		}

		$this->_filters[] = $callable;
	}

	public function getPagesForProduct(Product $product, array $options = null, $limit = null)
	{
		$return = array();

		$query = '
			SELECT
				page.page_id
			FROM
				page
			JOIN
				page_content AS product_content ON (
					page.page_id = product_content.page_id
				AND product_content.group_name = "product"
				AND product_content.field_name = "product"
				)
			LEFT JOIN
				page_content AS option_name_content ON (
					page.page_id = option_name_content.page_id
				AND option_name_content.group_name = "product"
				AND option_name_content.field_name = "option"
				AND option_name_content.data_name  = "name"
				)
			LEFT JOIN
				page_content AS option_value_content ON (
					page.page_id = option_value_content.page_id
				AND option_value_content.group_name = "product"
				AND option_value_content.field_name = "option"
				AND option_value_content.data_name  = "value"
				)
			WHERE
				page.type                        IN ("product", "strap")
			AND product_content.value_int         = :productID?i
			AND IF(:optionName?sn IS NOT NULL, option_name_content.value_string = :optionName?sn, 1)
			AND IF(:optionValue?sn IS NOT NULL, option_value_content.value_string = :optionValue?sn, 1)
			ORDER BY
				position_left ASC
		';

		$params = array(
			'productID'   => $product->id,
			'optionName'  => $optionName,
			'optionValue' => $optionValue,
		);

		if (null !== $limit) {
			$query .= ' LIMIT :limit';
			$params['limit'] = $limit;
		}

		$result = $this->_query->run($query, $params);

		// Filter out any pages not visible; not published or not to show on aggregator pages
		foreach ($this->_loader->getByID($result->flatten()) as $key => $page) {
			// Filter out any pages that aren't viewable or published
			if (!$this->_auth->isViewable($page)
			 || !$this->_auth->isPublished($page)) {
				continue;
			}

			// Run custom filters and remove any where the return value is falsey
			foreach ($this->_filters as $filter) {
				if (!$filter($page)) {
					continue 2;
				}
			}

			$return[$key] = $page;
		}

		return $return;
	}

	public function getPageForProduct(Product $product, array $options = null)
	{
		$pages = $this->getPagesForProduct($product, $options, 1);

		return array_shift($pages);
	}

	public function getUnitForProduct(Unit $unit)
	{
		return $this->getPageForProduct($unit->product, $unit->options);
	}
}