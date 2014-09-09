<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order;

use Message\Mothership\Commerce\Product\Events;


class ProductPageListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return [
			Events::PRODUCT_UPLOAD_CREATE => [
				'createProductPage'
			]
		];
	}

	public function createProductPage()
	{

	}
}