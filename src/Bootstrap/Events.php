<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Cog\Bootstrap\EventsInterface;
use Message\Mothership\Ecommerce\EventListener;

class Events implements EventsInterface
{
	public function registerEvents($dispatcher)
	{
		$dispatcher->addSubscriber(new \Message\Mothership\Ecommerce\EventListener\CheckoutListener);
		$dispatcher->addSubscriber(new \Message\Mothership\Ecommerce\EventListener\OrderListener);
		$dispatcher->addSubscriber(new \Message\Mothership\Ecommerce\EventListener\DashboardListener);
		$dispatcher->addSubscriber(new \Message\Mothership\Ecommerce\EventListener\ProductPageListener);
		$dispatcher->addSubscriber(new EventListener);
	}
}