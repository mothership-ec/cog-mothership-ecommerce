<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.ecom']->setParent('ms.cp')->setPrefix('/order/fulfillment');

		$router['ms.ecom']->add('ms.ecom.fulfillment', '/', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Fulfillment#index');

		$router['ms.ecom']->add('ms.ecom.fulfillment.new', '/new', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Fulfillment#newOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.active', '/active', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Fulfillment#activeOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.pick', '/pick', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Fulfillment#pickOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.pack', '/pack', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Fulfillment#packOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.post', '/post', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Fulfillment#postOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.pickup', '/pickup', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Fulfillment#pickupOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.print.slip', '/process/print/slip', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#printSlip')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.print.action', '/process/print', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#printAction')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.print', '/process/print/{orderID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#printOrders')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pick.action', '/process/pick/{orderID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#pickAction')
			->setRequirement('orderID', '\d+')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pick', '/process/pick/{orderID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#pickOrders')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pack.action', '/process/pack/{orderID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#packAction')
			->setRequirement('orderID', '\d+')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pack', '/process/pack/{orderID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#packOrders')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.post.action', '/process/post/{orderID}/{dispatchID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#postAction')
			->setRequirement('orderID', '\d+')
			->setRequirement('dispatchID', '\d+')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.post', '/process/post/{orderID}/{dispatchID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#postOrders')
			->setRequirement('orderID', '\d+')
			->setRequirement('dispatchID', '\d+');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.address', '/process/address/{orderID}/{dispatchID}/{addressID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#amendAddress')
			->setRequirement('orderID', '\d+')
			->setRequirement('dispatchID', '\d+')
			->setRequirement('addressID', '\d+')
			->setMethod('GET');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.address.action', '/process/address/{orderID}/{dispatchID}/{addressID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#amendAddressAction')
			->setRequirement('orderID', '\d+')
			->setRequirement('dispatchID', '\d+')
			->setRequirement('addressID', '\d+')
			->setMethod('POST');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.post.auto', '/process/post/{orderID}/{dispatchID}/automatic', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#postAutomatically')
			->setRequirement('orderID', '\d+')
			->setRequirement('dispatchID', '\d+')
			->setFormat('json');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pickup.action', '/process/post/{orderID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#pickupOrders')
			->setRequirement('orderID', '\d+')
			->setMethod('POST');
		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pickup.action', '/process/pickup', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#pickupAction');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pickup', '/process/pickup/{orderID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Process#pickupOrders')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom']->add('ms.ecom.fulfillment.picking.view', '/process/packing/{orderID}/{documentID}', 'Message:Mothership:Ecommerce::Controller:Fulfillment:Picking#view')
			->setRequirement('orderID', '\d+')
			->setRequirement('documentID', '\d+');

		$router['ms.ecom.register']->add('ms.ecom.register.action', '/register', 'Message:Mothership:Ecommerce::Controller:Account:Register#registerProcess')
			->setMethod('POST');
	}
}