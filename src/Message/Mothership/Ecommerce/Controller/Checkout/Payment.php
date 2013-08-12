<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Mothership\Ecommerce\Form\UserDetails;
use Message\Cog\Controller\Controller;
use Message\User\User;
use Message\User\AnonymousUser;
/**
 * Class Checkout/Delivery
 */
class Payment extends Controller
{
	public function index()
	{
		$gateway = $this->get('commerce.gateway');
		$order = $this->get('basket')->getOrder();

		$gateway->setUsername('uniformwareslim');
		$gateway->getGateway()->setSimulatorMode(false);
		$gateway->getGateway()->setTestMode(true);

		$billing = array_pop($order->addresses->getByProperty('type', 'billing'));
		$delivery = array_pop($order->addresses->getByProperty('type', 'delivery'));

		$gateway->setBillingAddress($billing);
		$gateway->setDeliveryAddress($delivery);
		$gateway->setOrder($order);
		$gateway->setPaymentAmount($order->totalGross, $order->currencyID);
		$gateway->setRedirectUrl('http://82.44.182.93/checkout/payment/process');

		$response = $gateway->send();

		$gateway->saveResponse();

		if ($response->isSuccessful()) {
		    // payment is complete
		} elseif ($response->isRedirect()) {
		    $response->redirect(); // this will automatically forward the customer
		} else {
		    // not successful
		}

		return $this->render('Message:Mothership:Ecommerce::Checkout:delivery', array(
			'form'    => $this->deliveryMethodForm(),
		));
	}

	public function response()
	{

		// $gateway = \Omnipay\Common\GatewayFactory::create('Sagepay_Server');
		// $settings = $gateway->getDefaultParameters();

		// $gateway->setVendor('uniformwareslim');
		// $gateway->setSimulatorMode(false);
		// $gateway->setTestMode(true);

		// $card = new \Omnipay\Common\CreditCard;
		// $card->setShippingFirstName('Danny');
		// $card->setShippingLastName('Hannah');
		// $card->setShippingAddress1('31 A Bukcingham street');
		// $card->setShippingAddress2('Line 2');
		// $card->setShippingCity('Brighton');
		// $card->setShippingPostcode('BN1 3LT');
		// $card->setShippingState('');
		// $card->setShippingCountry('United Kingdom');
		// $card->setShippingPhone('07702695391');
		// $card->setEmail('danny@message.co.uk');
		// $card->setFirstName('Danny');
		// $card->setLastName('Hannah');
		// $card->setAddress1('31 A Bukcingham street');
		// $card->setAddress2('Line 2');
		// $card->setCity('Brighton');
		// $card->setPostcode('BN1 3LT');
		// $card->setState('');
		// $card->setCountry('GB');
		// $card->setPhone('07702695391');

		mail('danny@message.co.uk','sagepay', print_r($_REQUEST,true));
		// $request = $_SESSION['request']->completePurchase(array(
		// 	'amount' => '10.00', // this represents $10.00
		// 	'card' => $card,
		// 	'currency' => 'GBP',
		// 	'returnUrl' => 'https://uniformwares.pagekite.me/checkout/payment/response',
		// 	'transactionId' => 29,
		// 	'description' => 'uniform wares payment',
		// ));
		// mail('danny@message.co.uk','sagepay', print_r($request,true));
		// $response = $request->send();
		// $response->confirm('http://82.44.182.93:8523'.$this->generateUrl('ms.ecom.checkout.payment.confirm'));

	}

	public function confirm()
	{
		de($_SERVER);
	}
}
