<?php

namespace Message\Mothership\Ecommerce\ProductPage;

use Message\Cog\Event\Dispatcher;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Mothership\CMS\Page\Page;

class ParentPageCreateEventDispatcher
{
	private $_dispatcher;

	public function __construct(Dispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
	}

	public function dispatch(PageTypeInterface $pageType, $name, Page $parent = null)
	{
		$event = new ParentPageCreateEvent($pageType, $name, $parent);

		return $this->_dispatcher->dispatch(
			Events::PRODUCT_PARENT_PAGE_CREATE,
			$event
		)->getPage();
	}
}