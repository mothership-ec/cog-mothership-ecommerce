<?php

namespace Message\Mothership\Ecommerce\Finder;

use Message\Cog\DB;
use Message\Mothership\CMS\Page;
use Message\Mothership\Commerce\Product;
use Message\Mothership\Commerce\Product\Unit;

/**
 * Provides a map between products and pages allowing a dynamic relationship
 * that can be redefined by an extension to this class.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class ProductPageMapper implements ProductPageFinderInterface, PageProductFinderInterface
{
	protected $_query;
	protected $_loader;
	protected $_auth;

	protected $_filters = array();

	/**
	 * Constructor.
	 *
	 * @param DB\Query           $query
	 * @param Page\Loader        $pageLoader
	 * @param Page\Authorisation $pageAuth
	 * @param Product\Loader     $productLoader
	 * @param Unit\Loader        $unitLoader
	 */
	public function __construct(
		DB\Query           $query,
		Page\Loader        $pageLoader,
		Page\Authorisation $pageAuth,
		Product\Loader     $productLoader,
		Unit\Loader        $unitLoader
	) {
		$this->_query         = $query;
		$this->_pageLoader    = $pageLoader;
		$this->_pageAuth      = $pageAuth;
		$this->_productLoader = $productLoader;
		$this->_unitLoader    = $unitLoader;
	}

	/**
	 * @{inheritDoc}
	 */
	public function addFilter($callable)
	{
		if (!is_callable($callable)) {
			throw new \InvalidArgumentException('Filters for ProductPageFinder must be callable.');
		}

		$this->_filters[] = $callable;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForUnit(Unit\Unit $unit)
	{
		// Initially try to find the page matched against the unit's options.
		if ($page = $this->getPageForProduct($unit->product, $unit->options)) {
			return $page;
		}

		// If no page is found with these specific options, instead fallback to
		// just matching the product.
		return $this->getPageForProduct($unit->product);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForProduct(Product\Product $product, array $options = null)
	{
		$pages = $this->getPagesForProduct($product, $options, 1);

		return count($pages) ? array_shift($pages) : false;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPagesForProduct(Product\Product $product, array $options = null, $limit = null)
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
				page.type IN ("product")
			AND product_content.value_int  = :productID?i
		';

		$params = array(
			'productID' => $product->id,
		);

		if (null !== $options) {
			$query .= ' AND option_name_content.value_string IN (:optionNames)';
			$query .= ' AND option_value_content.value_string IN (:optionValues)';
			$params['optionNames']  = "'".implode("','", array_keys($options))."'";
			$params['optionValues'] = "'".implode("','", array_values($options))."'";
		}

		$query .= ' ORDER BY position_left ASC';

		if (null !== $limit) {
			$query .= ' LIMIT :limit?i';
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

	/**
	 * @{inheritDoc}
	 */
	public function getUnitsForPage(Page\Page $page)
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
	public function getProductForPage(Page\Page $page)
	{
		return $this->getProductsForPage($page, 1);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductsForPage(Page\Page $page, $limit = null)
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