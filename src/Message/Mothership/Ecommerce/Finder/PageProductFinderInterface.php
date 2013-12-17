<?php

namespace Message\Mothership\Ecommerce\Finder;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;

interface PageProductFinderInterface
{
	/**
	 * Get the units associated with a page.
	 *
	 * @param  Page   $page
	 * @return array[Unit]
	 */
	public function getUnitsForPage(Page $page);

	/**
	 * Get the first product associated with a page.
	 *
	 * @param  Page   $page
	 * @return array[Product]
	 */
	public function getProductForPage(Page $page);

	/**
	 * Get the products associated with a page.
	 *
	 * @param  Page     $page
	 * @param  int|null $limit Limit number of pages to find
	 * @return array[Product]
	 */
	public function getProductsForPage(Page $page, $limit = null);
}