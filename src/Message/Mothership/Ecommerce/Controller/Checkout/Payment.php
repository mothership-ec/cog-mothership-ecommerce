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
		$gateway->setRedirectUrl('http://82.44.182.93/checkout/payment/response');

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
		$id = $_REQUEST['VPSTxId'];
		$gateway = $this->get('commerce.gateway');
		$gateway->setUsername('uniformwareslim');
		$gateway->getGateway()->setSimulatorMode(false);
		$gateway->getGateway()->setTestMode(true);

		$data = $gateway->handleResponse($id);

		try {
			$final = $gateway->completePurchase($data);
			$order = $this->get('order.create')->create($data['order']);
			$final->confirm('http://82.44.182.93'.$this->generateUrl('ms.ecom.checkout.payment.confirm', array('orderID' => $order->id)));
		} catch (\Exception $e) {
	    	header("Content-type: text/plain");
	    	echo 'Status=INVALID;RedirectURL=http://82.44.182.93/checkout/payment';
	    	exit;
		}
	}

	public function confirm($orderID)
	{
		$order = $this->get('order.loader')->getByID($orderID);

		return $this->render('Message:Mothership:Ecommerce::Checkout:success', array(
			'order'    => $order,
		));
	}
}
