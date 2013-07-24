<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.ecom']->setParent('ms.cp')->setPrefix('/sop');

		$router['ms.ecom']->add('ms.ecom.process.new', '/new', '::Controller:Process#newOrders');

		$router['ms.ecom']->add('ms.ecom.process.active', '/active', '::Controller:Process#activeOrders');

		$router['ms.ecom']->add('ms.ecom.process.pick', '/pick', '::Controller:Process#pickOrders');

		$router['ms.ecom']->add('ms.ecom.process.pack', '/pack', '::Controller:Process#packOrders');

		$router['ms.ecom']->add('ms.ecom.process.post', '/post', '::Controller:Process#postOrders');

		$router['ms.ecom']->add('ms.ecom.process.pickup', '/pickup', '::Controller:Process#pickupOrders');
	}
}