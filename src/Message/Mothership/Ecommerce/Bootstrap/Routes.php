<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.ecom']->setParent('ms.cp')->setPrefix('/fulfillment');

		$router['ms.ecom']->add('ms.ecom.fulfillment', '/', '::Controller:Fulfillment:Fulfillment#index');

		$router['ms.ecom']->add('ms.ecom.fulfillment.new', '/new', '::Controller:Fulfillment:Fulfillment#newOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.active', '/active', '::Controller:Fulfillment:Fulfillment#activeOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.pick', '/pick', '::Controller:Fulfillment:Fulfillment#pickOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.pack', '/pack', '::Controller:Fulfillment:Fulfillment#packOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.post', '/post', '::Controller:Fulfillment:Fulfillment#postOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.pickup', '/pickup', '::Controller:Fulfillment:Fulfillment#pickupOrders');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.print.action', '/process/print', '::Controller:Fulfillment:Process#printSlip')
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

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pickup.action', '/process/post/{orderID}', '::Controller:Fulfillment:Process#pickupOrders')
			->setRequirement('orderID', '\d+')
			->setMethod('POST');
		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pickup.action', '/process/pickup', '::Controller:Fulfillment:Process#pickupAction');

		$router['ms.ecom']->add('ms.ecom.fulfillment.process.pickup', '/process/pickup/{orderID}', '::Controller:Fulfillment:Process#pickupOrders')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom.checkout']->setPrefix('/checkout');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.action', '/', '::Controller:Checkout:Checkout#process')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.discount', '/', '::Controller:Checkout:Checkout#discountProcess')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.voucher', '/', '::Controller:Checkout:Checkout#voucherProcess')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.remove', '/remove/{unitID}', '::Controller:Checkout:Checkout#removeUnit')
			->setMethod('GET')
			->enableCsrf('csrfHash');

		$router['ms.ecom.checkout']->add('ms.ecom.basket.empty', '/basket/empty', '::Controller:Module:Basket#emptyBasket');

		$router['ms.ecom.checkout']->add('ms.ecom.checkout', '/', '::Controller:Checkout:Checkout#index');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.details', '/personal/details', '::Controller:Checkout:Details#index');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.details.addresses.action', '/details/addresses', '::Controller:Checkout:Details#addressProcess')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.details.addresses', '/details/addresses', '::Controller:Checkout:Details#addresses');

		$router['ms.ecom.account']->setPrefix('/account');
		$router['ms.ecom.account']->add('ms.ecom.account', '/', '::Controller:Account:Account#index');


	}
}