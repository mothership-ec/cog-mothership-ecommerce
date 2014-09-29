<?php

namespace Message\Mothership\Ecommerce\ProductPage;

use Message\Cog\Event\Dispatcher;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;

class ProductPageCreateEventDispatcher
{
	public function __construct(Dispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
	}

	public function dispatch(Page $page, Product $product, $csvPort = false, Unit $unit = null)
	{
		$event = new ProductPageCreateEvent($page, $product, $csvPort, $unit);

		return $this->_dispatcher->dispatch(
			Events::PRODUCT_PAGE_CREATE,
			$event
		)->getPage();
	}
}