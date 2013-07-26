<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.ecom']->setParent('ms.cp')->setPrefix('/sop');

		$router['ms.ecom']->add('ms.ecom.sop.new', '/new', '::Controller:Sop#newOrders');

		$router['ms.ecom']->add('ms.ecom.sop.active', '/active', '::Controller:Sop#activeOrders');

		$router['ms.ecom']->add('ms.ecom.sop.pick', '/pick', '::Controller:Sop#pickOrders');

		$router['ms.ecom']->add('ms.ecom.sop.pack', '/pack', '::Controller:Sop#packOrders');

		$router['ms.ecom']->add('ms.ecom.sop.post', '/post', '::Controller:Sop#postOrders');

		$router['ms.ecom']->add('ms.ecom.sop.pickup', '/pickup', '::Controller:Sop#pickupOrders');
	}
}