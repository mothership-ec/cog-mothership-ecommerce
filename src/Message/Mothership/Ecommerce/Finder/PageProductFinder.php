<?php

namespace Message\Mothership\Ecommerce\Finder;

use Message\Cog\DB;
use Message\Mothership\CMS\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;

class PageProductFinder implements PageProductFinderInterface
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
		return;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductForPage(Page $page)
	{
		return;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductsForPage(Page $page)
	{
		return;
	}
}