<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.ecom']->setParent('ms.cp')->setPrefix('/fulfillment');

		$router['ms.ecom']->add('ms.ecom.fulfillment', '/', '::Controller:Fulfillment#index');

		$router['ms.ecom']->add('ms.ecom.fulfillment.new', '/new', '::Controller:Fulfillment#newOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.active', '/active', '::Controller:Fulfillment#activeOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.pick', '/pick', '::Controller:Fulfillment#pickOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.pack', '/pack', '::Controller:Fulfillment#packOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.post', '/post', '::Controller:Fulfillment#postOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.pickup', '/pickup', '::Controller:Fulfillment#pickupOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.print.action', '/process/print', '::Controller:Fulfillment:Process#printAction')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.print', '/process/print/{orderID}', '::Controller:Fulfillment:Process#printOrders')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pick.action', '/process/pick/{orderID}', '::Controller:Fulfillment:Process#pickAction')
			->setRequirement('orderID', '\d+')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pick', '/process/pick/{orderID}', '::Controller:Fulfillment:Process#pickOrders')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pack.action', '/process/pack/{orderID}', '::Controller:Fulfillment:Process#packAction')
			->setRequirement('orderID', '\d+')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pack', '/process/pack/{orderID}', '::Controller:Fulfillment:Process#packOrders')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.post.action', '/process/post/{orderID}', '::Controller:Fulfillment:Process#postAction')
			->setRequirement('orderID', '\d+')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.post', '/process/post/{orderID}', '::Controller:Fulfillment:Process#postOrders')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pickup.action', '/process/pickup', '::Controller:Fulfillment:Process#pickupAction');;

//		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pickup', '/process/pickup/{orderID}', '::Controller:Fulfillment:Process#pickupOrders')
//			->setRequirement('orderID', '\d+');
	}
}