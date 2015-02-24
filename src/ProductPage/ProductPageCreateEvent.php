<?php

namespace Message\Mothership\Ecommerce\ProductPage;

use Message\Cog\Event\Event;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;

class ProductPageCreateEvent extends Event
{
	/**
	 * @var Page
	 */
	private $_page;

	/**
	 * @var Product
	 */
	private $_product;

	/**
	 * @var Unit
	 */
	private $_unit;

	/**
	 * @var bool
	 */
	private $_csvPort;

	public function __construct(Page $page, Product $product = null, $csvPort = false, Unit $unit = null)
	{
		$this->_csvPort = (bool) $csvPort;

		$this->setPage($page);
		$this->setProduct($product);

		if ($unit) {
			$this->setUnit($unit);
		}
	}

	/**
	 * @param \Message\Mothership\CMS\Page\Page $page
	 *
	 * @return ProductPageCreateEvent         return $this for chainability
	 */
	public function setPage(Page $page)
	{
		$this->_page = $page;

		return $this;
	}

	/**
	 * @return \Message\Mothership\CMS\Page\Page
	 */
	public function getPage()
	{
		return $this->_page;
	}

	/**
	 * @param \Message\Mothership\Commerce\Product\Product $product
	 *
	 * @return ProductPageCreateEvent         return $this for chainability
	 */
	public function setProduct(Product $product = null)
	{
		$this->_product = $product;

		return $this;
	}

	/**
	 * @return \Message\Mothership\Commerce\Product\Product
	 */
	public function getProduct()
	{
		return $this->_product;
	}

	/**
	 * @param \Message\Mothership\Commerce\Product\Unit\Unit $unit
	 *
	 * @return ProductPageCreateEvent         return $this for chainability
	 */
	public function setUnit(Unit $unit)
	{
		$this->_unit = $unit;

		return $this;
	}

	/**
	 * @return \Message\Mothership\Commerce\Product\Unit\Unit
	 */
	public function getUnit()
	{
		return $this->_unit;
	}

	public function isCsvPort()
	{
		return $this->_csvPort;
	}
}