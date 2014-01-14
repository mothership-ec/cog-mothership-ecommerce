<?php

namespace Message\Mothership\Ecommerce\Templating;

use Twig_Extension;
use Twig_SimpleFunction;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Ecommerce\ProductPageMapper\AbstractMapper;
use Message\Mothership\Ecommerce\ProductPageMapper\ProductPageFinderInterface;
use Message\Mothership\Ecommerce\ProductPageMapper\PageProductFinderInterface;

class ProductPageMapperTwigExtension extends Twig_Extension implements ProductPageFinderInterface, PageProductFinderInterface
{
	protected $_mapper;

	/**
	 * Constructor.
	 *
	 * @param AbstractMapper $mapper
	 */
	public function __construct(AbstractMapper $mapper)
	{
		$this->_mapper = $mapper;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getFunctions()
	{
		return array(
			new Twig_SimpleFunction('getPageForProductUnit',  array($this, 'getPageForProductUnit')),
			new Twig_SimpleFunction('getPageForProduct',      array($this, 'getPageForProduct')),
			new Twig_SimpleFunction('getPagesForProduct',     array($this, 'getPagesForProduct')),
			new Twig_SimpleFunction('getProductUnitsForPage', array($this, 'getProductUnitsForPage')),
			new Twig_SimpleFunction('getProductForPage',      array($this, 'getProductForPage')),
			new Twig_SimpleFunction('getProductsForPage',     array($this, 'getProductsForPage')),
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getName()
	{
		return 'product_page_mapper';
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForProductUnit(Unit $unit)
	{
		return $this->_mapper->getPageForProductUnit($unit);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForProduct(Product $product, array $options = null)
	{
		return $this->_mapper->getPageForProduct($product, $options);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPagesForProduct(Product $product, array $options = null, $limit = null)
	{
		return $this->_mapper->getPagesForProduct($product, $options, $limit);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductUnitsForPage(Page $page)
	{
		return $this->_mapper->getProductUnitsForPage($page);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductForPage(Page $page)
	{
		return $this->_mapper->getProductForPage($page);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductsForPage(Page $page, $limit = null)
	{
		return $this->_mapper->getProductsForPage($page, $limit);
	}
}