<?php

namespace Message\Mothership\Ecommerce\Bootstrap\Routes;

use Message\Cog\Bootstrap\RoutesInterface;

class Checkout implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.ecom.checkout']->setPrefix('/checkout');

		// Basket
		$router['ms.ecom.checkout']->add('ms.ecom.basket.empty', '/empty', 'Message:Mothership:Ecommerce::Controller:Module:Basket#emptyBasket');


		// Checkout
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


		// Details
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.details', '/details', 'Message:Mothership:Ecommerce::Controller:Checkout:Details#index');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.details.register', '/details/register', 'Message:Mothership:Ecommerce::Controller:Checkout:Details#register');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.details.addresses', '/details/addresses', 'Message:Mothership:Ecommerce::Controller:Checkout:Details#addresses');


		// Confirm
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.confirm.action', '/confirm', 'Message:Mothership:Ecommerce::Controller:Checkout:Confirm#processContinue')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.confirm.delivery.action', '/confirm/delivery-method', 'Message:Mothership:Ecommerce::Controller:Checkout:FinalCheck#processDeliveryMethod')
			->setMethod('POST');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.confirm', '/confirm', 'Message:Mothership:Ecommerce::Controller:Checkout:Confirm#index');


		// Result
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.payment.unsuccessful', '/payment/unsuccessful', 'Message:Mothership:Ecommerce::Controller:Checkout:Complete#unsuccessful');
		$router['ms.ecom.checkout']->add('ms.ecom.checkout.payment.successful', '/payment/successful/{orderID}/{hash}', 'Message:Mothership:Ecommerce::Controller:Checkout:Complete#successful')
			->setRequirement('orderID', '\d+');
	}
}