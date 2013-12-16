<?php

use Message\Mothership\CMS\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;

interface ProductPageFinderInterface
{
	/**
	 * Get the left-most page for a unit.
	 *
	 * @param  Unit $unit
	 * @return Page
	 */
	public function getPageForUnit(Unit $unit);

	/**
	 * Get the left-most page for a product.
	 *
	 * @param  Product    $product
	 * @param  array|null $options Name => Value array, e.g. 'Colour' => 'Red'
	 * @return Page
	 */
	public function getPageForProduct(Product $product, array $options = null);

	/**
	 * Get all pages associated with a product.
	 *
	 * @param  Product    $products
	 * @param  array|null $options  Name => Value array, e.g. 'Colour' => 'Red'
	 * @param  int|null   $limit    Limit number of pages to find
	 * @return Page
	 */
	public function getPagesForProduct(Product $products, array $options = null, $limit = null);
}