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
	/**
	 * Handles payment gateway or local payment initiation
	 */
	public function index()
	{
		// If in local mode then bypass the payment gateway
		// The `use local payments` config also needs to be true
		if ($this->get('environment')->isLocal()
		 && $this->get('cfg')->checkout->payment->useLocalPayments
		) {
			return $this->localPayment();
		}

		$gateway  = $this->get('commerce.gateway');
		$config   = $this->_services['cfg']['checkout']->payment;
		$order    = $this->get('basket')->getOrder();

		$billing  = $order->getAddress('billing');
		$delivery = $order->getAddress('delivery');

		$gateway->setUsername($config->username);
		$gateway->getGateway()->setTestMode($config->useTestPayments);

		$gateway->setBillingAddress($billing);
		$gateway->setDeliveryAddress($delivery);
		$gateway->setOrder($order);
		$gateway->setPaymentAmount($order->totalGross, $order->currencyID);
		$gateway->setRedirectUrl('http://82.44.182.93/checkout/payment/response');

		$response = $gateway->send();
		$gateway->saveResponse();

		if ($response->isRedirect()) {
		    $response->redirect();
		} else {
			$this->addFlash('error', 'Couldn\'t connect to payment gateway');
		}

		return $this->redirectToRoute('ms.ecom.checkout.delivery');
	}

	/**
	 * Handles the response from the payment gateway after payment
	 */
	public function response()
	{
		$id = $this->get('request')->get('VPSTxId');
		$gateway = $this->get('commerce.gateway');
		$gateway->setUsername('uniformwareslim');
		$gateway->getGateway()->setSimulatorMode(false);
		$gateway->getGateway()->setTestMode(true);

		$data = $gateway->handleResponse($id);

		try {
			$final = $gateway->completePurchase($data);

			$paymentMethod = $this->get('order.payment.methods')->get('card');
			$this->get('basket')->setOrder($data['order']);
			$this->get('basket')->addPayment($paymentMethod, $order->totalGross, '');

			$order = $this->get('order.create')->create($this->get('basket')->getOrder());
			$salt  = $this->_services['cfg']['checkout']->payment->salt;

			$final->confirm('http://82.44.182.93'.$this->generateUrl('ms.ecom.checkout.payment.confirm', array(
				'orderID' => $order->id,
				'hash' => $this->get('security.hash')->encrypt($order->id, $salt),
			)));

		} catch (\Exception $e) {
	    	header("Content-type: text/plain");
	    	echo 'Status=INVALID;RedirectURL=http://82.44.182.93/checkout/payment';
	    	exit;
		}
	}

	/**
	 * Load the order for the order confirmation page
	 *
	 * @param  int 		$orderID 	confirmed orderID to laod and display
	 * @param  string 	$hash   	hash to ensure we only display the order page to good people
	 *
	 * @return View 				order confirmation page
	 */
	public function confirm($orderID, $hash)
	{
		// Get the salt and generate a new hash based on the given order number
		$salt = $this->_services['cfg']['checkout']->payment->salt;
		$generatedHash = $this->get('security.hash')->encrypt($orderID, $salt);

		// Check that the generated hash and the passed through hashes match
		if ($hash != $generatedHash) {
			throw new \Exception('Order hash doesn\'t match');
		}
		// Get the order
		$order = $this->get('order.loader')->getByID($orderID);
		// Clear the basket
		$this->get('basket')->empty();

		return $this->render('Message:Mothership:Ecommerce::Checkout:success', array(
			'order'    => $order,
		));
	}

	/**
	 * Handle local payments for testing on local envirnments
	 * This just bypasses the payment gateway but still creates an order
	 */
	public function localPayment()
	{
		// Set the payment type as manual for now for local payments
		$paymentMethod = $this->get('order.payment.methods')->get('manual');
		// Get the order
		$order = $this->get('basket')->getOrder();
		// Add the payment to the basket order
		$this->get('basket')->addPayment($paymentMethod, $order->totalGross, 'local payment');

		// Save the order
		$order = $this->get('order.create')->create($this->get('basket')->getOrder());
		// Get the salt
		$salt  = $this->_services['cfg']['checkout']->payment->salt;
		// Generate a hash and set the redirect url
		$url = $this->generateUrl('ms.ecom.checkout.payment.confirm', array(
			'orderID' => $order->id,
			'hash' => $this->get('security.hash')->encrypt($order->id, $salt)
		));

		return $this->redirect($url);
	}
}
