<?php

interface ProductPageFinderInterface
{
	public function getPageForUnit(Unit $unit);
	public function getPageForProduct(Product $product, array $options = null);
	public function getPagesForProduct(Product $products, array $options = null, $limit = null);
}