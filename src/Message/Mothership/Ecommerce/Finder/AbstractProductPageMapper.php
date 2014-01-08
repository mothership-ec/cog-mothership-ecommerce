<?php

namespace Message\Mothership\Ecommerce\Finder;

use Message\Cog\DB;
use Message\Mothership\CMS\Page;
use Message\Mothership\Commerce\Product;
use Message\Mothership\Commerce\Product\Unit;

/**
 * Abstract product page mapper for defining the relationship between products
 * and pages.
 *
 * Child classes should implement the `getPagesForProduct` and
 * `getProductsForPage` methods.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
abstract class ProductPageMapper implements ProductPageFinderInterface, PageProductFinderInterface
{
	protected $_query;
	protected $_pageLoader;
	protected $_pageAuth;
	protected $_productLoader;
	protected $_unitLoader;

	protected $_validFieldNames;
	protected $_validGroupNames;

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

	public function setValidGroupName($group)
	{
		if (!is_array($group)) $group = array($group);

		$this->_validGroupNames = $group;
	}

	public function setValidFieldName($field)
	{
		if (!is_array($field)) $field = array($field);

		$this->_validFieldNames = $field;
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
	abstract public function getPagesForProduct(Product\Product $product, array $options = null, $limit = null);

	/**
	 * @{inheritDoc}
	 */
	abstract public function getProductsForPage(Page\Page $page, $limit = null);

	/**
	 * Load pages from a query and parameters.
	 *
	 * @param  string      $query
	 * @param  array       $params
	 * @return array[Page]
	 */
	protected function _loadPages($query, $params)
	{
		$return = [];

		$result = $this->_query->run($query, $params);

		// Filter out any pages not visible; not published or not to show on aggregator pages
		foreach ($this->_pageLoader->getByID($result->flatten()) as $key => $page) {
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
	 * Load products from a query and parameters.
	 *
	 * @param  string         $query
	 * @param  array          $params
	 * @return array[Product]
	 */
	protected function _loadProducts($query, $params)
	{
		$return = [];

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