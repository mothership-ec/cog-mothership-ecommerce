<?php

namespace Message\Mothership\Ecommerce\Templating;

use Twig_Extension;
use Twig_SimpleFunction;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Ecommerce\Finder\PageProductFinderInterface;

class PageProductFinderTwigExtension extends Twig_Extension implements PageProductFinderInterface
{
	protected $_finder;

	/**
	 * Constructor.
	 *
	 * @param PageProductFinderInterface $finder
	 */
	public function __construct(PageProductFinderInterface $finder)
	{
		$this->_finder = $finder;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getFunctions()
	{
		return array(
			new Twig_SimpleFunction('getUnitsForPage',    array($this, 'getUnitsForPage')),
			new Twig_SimpleFunction('getProductForPage',  array($this, 'getProductForPage')),
			new Twig_SimpleFunction('getProductsForPage', array($this, 'getProductsForPage')),
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getName()
	{
		return 'page_product_finder';
	}

	/**
	 * @{inheritDoc}
	 */
	public function getUnitsForPage(Page $page)
	{
		return $this->_finder->getUnitsForPage($page);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductForPage(Page $page)
	{
		return $this->_finder->getProductForPage($page);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductsForPage(Page $page, $limit = null)
	{
		return $this->_finder->getProductsForPage($page, $limit);
	}
}