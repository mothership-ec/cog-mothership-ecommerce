<?php

namespace Message\Mothership\Ecommerce\Finder;

use Message\Cog\DB;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product;
use Message\Mothership\Commerce\Product\Unit;

class PageProductFinder implements PageProductFinderInterface
{
	protected $_query;
	protected $_loader;
	protected $_auth;

	protected $_filters = array();

	public function __construct(DB\Query $query, Product\Loader $productLoader, Unit\Loader $unitLoader)
	{
		$this->_query         = $query;
		$this->_productLoader = $productLoader;
		$this->_unitLoader    = $unitLoader;
	}

	/**
	 * @{inheritDoc}
	 */
	public function addFilter($callable)
	{
		if (!is_callable($callable)) {
			throw new \InvalidArgumentException('Filters for PageProductFinder must be callable.');
		}

		$this->_filters[] = $callable;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getUnitsForPage(Page $page)
	{
		$return = array();

		$products = $this->getProductsForPage($page);

		$this->_unitLoader->includeInvisible(true);
		$this->_unitLoader->includeOutOfStock(true);

		foreach ($products as $product) {
			if ($units = $this->_unitLoader->getByProduct($product)) {
				$return += $units;
			}
		}

		return $return;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductForPage(Page $page)
	{
		return $this->getProductsForPage($page, 1);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductsForPage(Page $page, $limit = null)
	{
		$return = array();

		$query = '
			SELECT
				product.product_id
			FROM
				product
			JOIN
				page_content ON (
					page_content.value_int = product.product_id
				AND page_content.group_name = "product"
				AND page_content.field_name = "product"
				)
			LEFT JOIN
				page ON (
					page.page_id = page_content.page_id
				)
			WHERE
				page.page_id = :pageID?i
			ORDER BY
				product.product_id ASC
		';

		$params = array(
			'pageID' => $page->id,
		);

		if (null !== $limit) {
			$query .= ' LIMIT :limit?i';
			$params['limit'] = $limit;
		}

		$result = $this->_query->run($query, $params);

		foreach ($this->_productLoader->getByID($result->flatten()) as $key => $product) {

			// Run custom filters and remove any where the return value is falsey
			foreach ($this->_filters as $filter) {
				if (!$filter($product)) {
					continue 2;
				}
			}

			$return[$key] = $product;
		}

		return $return;
	}
}