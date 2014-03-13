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

		$router['ms.ecom.checkout']->setPrefix('/checkout');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.action', '/', 'Message:Mothership:Ecommerce::Controller:Checkout:Checkout#process')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.discount', '/', 'Message:Mothership:Ecommerce::Controller:Checkout:Checkout#discountProcess')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.voucher', '/', 'Message:Mothership:Ecommerce::Controller:Checkout:Checkout#voucherProcess')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.remove', '/remove/{unitID}', 'Message:Mothership:Ecommerce::Controller:Checkout:Checkout#removeUnit')
			->setMethod('GET')
			->enableCsrf('csrfHash');

		$router['ms.ecom.checkout']->add('ms.ecom.checkout', '/', 'Message:Mothership:Ecommerce::Controller:Checkout:Checkout#index');
		$router['ms.ecom.checkout']->add('ms.ecom.basket.empty', '/empty', 'Message:Mothership:Ecommerce::Controller:Module:Basket#emptyBasket');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.details', '/details', 'Message:Mothership:Ecommerce::Controller:Checkout:Details#index');


		$router['ms.ecom.checkout']->add('ms.ecom.checkout.details.register', '/details/register', 'Message:Mothership:Ecommerce::Controller:Checkout:Details#register');

		$router['ms.ecom.checkout']->add('ms.ecom.checkout.details.addresses', '/details/addresses', 'Message:Mothership:Ecommerce::Controller:Checkout:Details#addresses');


		$router['ms.ecom.checkout']->add('ms.ecom.checkout.confirm.action', '/confirm', 'Message:Mothership:Ecommerce::Controller:Checkout:FinalCheck#processContinue')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.confirm.delivery.action', '/confirm/delivery-method', 'Message:Mothership:Ecommerce::Controller:Checkout:FinalCheck#processDeliveryMethod')

			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.confirm', '/confirm', 'Message:Mothership:Ecommerce::Controller:Checkout:FinalCheck#index');

		$router['ms.ecom.checkout']->add('ms.ecom.checkout.payment', '/payment', 'Message:Mothership:Ecommerce::Controller:Checkout:Payment#index');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.payment.response', '/payment/response', 'Message:Mothership:Ecommerce::Controller:Checkout:Payment#response');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.payment.unsuccessful', '/payment/unsuccessful', 'Message:Mothership:Ecommerce::Controller:Checkout:Payment#unsuccessful');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.payment.successful', '/payment/successful/{orderID}/{hash}', 'Message:Mothership:Ecommerce::Controller:Checkout:Payment#successful')
			->setRequirement('orderID', '\d+');

		$router['ms.ecom.register']->add('ms.ecom.register.action', '/regsiter', 'Message:Mothership:Ecommerce::Controller:Account:Register#registerProcess')
			->setMethod('POST');

		$router['ms.ecom.account']->setPrefix('/account');
		$router['ms.ecom.account']->add('ms.ecom.account', '/', 'Message:Mothership:Ecommerce::Controller:Account:Account#index');
		$router['ms.ecom.account']->add('ms.ecom.order.listing', '/orders', 'Message:Mothership:Ecommerce::Controller:Account:Account#orderListing');
		$router['ms.ecom.account']->add('ms.ecom.order.detail', '/orders/view/{orderID}', 'Message:Mothership:Ecommerce::Controller:Account:Account#orderDetail')
			->setMethod('GET');


	}
}