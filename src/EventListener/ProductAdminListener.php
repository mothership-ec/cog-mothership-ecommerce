<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Mothership\Commerce;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\ControlPanel\Event\BuildMenuEvent;

/**
 * Product admin event listener
 *
 * @author Samuel Trangmar-Keates <sam@message.co.uk>
 */
class ProductAdminListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return [
			Commerce\Events::PRODUCT_ADMIN_TAB_BUILD => [
				['buildProductMenu'],
			],
		];
	}

	public function buildProductMenu(BuildMenuEvent $event)
	{
		$current = $this->get('http.request.master')->get('_route');

		$event->addItem('ms.product.pages.list', 'Pages', [], $current == 'ms.product.pages.list' ? ['active'] : []);
	}
}