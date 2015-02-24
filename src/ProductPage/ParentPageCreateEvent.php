<?php

namespace Message\Mothership\Ecommerce\ProductPage;

use Message\Cog\Event\Event;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;

/**
 * Class ParentPageCreateEvent
 * @package Message\Mothership\Ecommerce\ProductPage
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class ParentPageCreateEvent extends Event
{
	/**
	 * @var PageTypeInterface
	 */
	private $_pageType;

	/**
	 * @var Page
	 */
	private $_page;

	/**
	 * @var Product
	 */
	private $_product;

	/**
	 * @var string
	 */
	private $_pageName;

	/**
	 * @var Page
	 */
	private $_parent;

	public function __construct(PageTypeInterface $pageType, $name, Product $product, Page $parent = null)
	{
		$this->setPageType($pageType);
		$this->setPageName($name);
		$this->setProduct($product);
		$this->setParent($parent);
	}

	/**
	 * @param string $pageName
	 * @throws \InvalidArgumentException
	 *
	 * @return ParentPageCreateEvent         return $this for chainability
	 */
	public function setPageName($pageName)
	{
		if (!is_string($pageName)) {
			throw new \InvalidArgumentException('Page name must be a string!');
		}

		$this->_pageName = $pageName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPageName()
	{
		return $this->_pageName;
	}

	/**
	 * @param PageTypeInterface $pageType
	 *
	 * @return ParentPageCreateEvent         return $this for chainability
	 */
	public function setPageType(PageTypeInterface $pageType)
	{
		$this->_pageType = $pageType;

		return $this;
	}

	/**
	 * @return PageTypeInterface
	 */
	public function getPageType()
	{
		return $this->_pageType;
	}

	/**
	 * @param Product $product
	 */
	public function setProduct(Product $product)
	{
		$this->_product = $product;
	}

	/**
	 * @return Product
	 */
	public function getProduct()
	{
		return $this->_product;
	}

	/**
	 * @param Page $parent
	 *
	 * @return ParentPageCreateEvent         return $this for chainability
	 */
	public function setParent(Page $parent = null)
	{
		$this->_parent = $parent;

		return $this;
	}

	/**
	 * @return Page
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * @param Page $page
	 */
	public function setPage(Page $page)
	{
		$this->_page = $page;
	}

	/**
	 * @throws \LogicException
	 *
	 * @return Page
	 */
	public function getPage()
	{
		if (null === $this->_page) {
			throw new \LogicException('Page not set on event');
		}

		return $this->_page;
	}
}