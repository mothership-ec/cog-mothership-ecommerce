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
		$dispatcher->addSubscriber(new EventListener);


		$dispatcher->addListener('kernel.controller', function($event) {
			if ($event->getController() instanceof Controller\Filter\GatewayIsActiveFilter) {
				if ($event->getController()->getGatewayName() !== $c['cfg']->gateway->gateway) {
					throw new NotFOundException('no');
				}
			}
		});
	}
}