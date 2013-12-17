<?php

namespace Message\Mothership\Ecommerce\Templating;

use Twig_Extension;
use Twig_SimpleFunction;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Ecommerce\Finder\ProductPageFinderInterface;

class ProductPageFinderTwigExtension extends Twig_Extension implements ProductPageFinderInterface
{
	protected $_finder;

	/**
	 * Constructor.
	 *
	 * @param ProductPageFinderInterface $finder
	 */
	public function __construct(ProductPageFinderInterface $finder)
	{
		$this->_finder = $finder;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getFunctions()
	{
		return array(
			new Twig_SimpleFunction('getPageForUnit',     array($this, 'getPageForUnit')),
			new Twig_SimpleFunction('getPageForProduct',  array($this, 'getPageForProduct')),
			new Twig_SimpleFunction('getPagesForProduct', array($this, 'getPagesForProduct')),
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getName()
	{
		return 'product_page_finder';
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForUnit(Unit $unit)
	{
		return $this->_finder->getPageForUnit($unit);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForProduct(Product $product, array $options = null)
	{
		return $this->_finder->getPageForProduct($product, $options);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPagesForProduct(Product $product, array $options = null, $limit = null)
	{
		return $this->_finder->getPagesForProduct($product, $options, $limit);
	}
}