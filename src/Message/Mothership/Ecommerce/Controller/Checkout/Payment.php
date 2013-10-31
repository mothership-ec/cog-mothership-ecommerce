<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Mothership\Ecommerce\Form\UserDetails;
use Message\Cog\Controller\Controller;
use Message\User\User;
use Message\User\AnonymousUser;
use Message\Cog\Event\Event;

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
		if (!$this->get('basket')->getOrder()->shippingName) {
			$this->addFlash('warning','You must select a delivery method before continuing.');

			return $this->redirectToRoute('ms.ecom.checkout.confirm');
		}

		// If in local mode then bypass the payment gateway
		// The `useLocalPayments` config also needs to be true
		if ($this->get('environment')->isLocal()
		 && $this->get('cfg')->checkout->payment->useLocalPayments
		) {
			return $this->zeroPayment('Local Payment', true);
		}

		$impersonateData = (array) $this->get('http.session')->get('impersonate.data');
		$impersonateSkip = (array_key_exists('order_skip_payment', $impersonateData)) ?
			(bool) $impersonateData['order_skip_payment'] : false;

		// If this user is being impersonated by an admin, skip the payment
		if ($this->get('http.session')->get('impersonate.impersonateID') == $this->get('user.current')->id and
			$impersonateSkip
		) {
			return $this->zeroPayment('Local Payment', true);
		}

		// Check for payments already applied to the order, if zero left to pay
		// then create the order
		if ($this->get('basket')->getOrder()->getAmountDue() == 0) {
			return $this->zeroPayment();
		}

		$gateway  = $this->get('commerce.gateway');
		$config   = $this->get('cfg')->checkout->payment;

		$order    = $this->get('basket')->getOrder();

		$billing  = $order->getAddress('billing');
		$delivery = $order->getAddress('delivery');

		$gateway->setUsername($config->username);
		$gateway->getGateway()->setTestMode((bool) $config->useTestPayments);

		$gateway->setBillingAddress($billing);
		$gateway->setDeliveryAddress($delivery);
		$gateway->setOrder($order);
		$gateway->setPaymentAmount($order->totalGross, $order->currencyID);
		$gateway->setRedirectUrl($this->getUrl().'/checkout/payment/response');

		$response = $gateway->send();
		$gateway->saveResponse();

		if ($response->isRedirect()) {
		    $response->redirect();
		} else {
			$this->addFlash('error', 'Couldn\'t connect to payment gateway');
		}

		return $this->redirectToRoute('ms.ecom.checkout.confirm');
	}

	/**
	 * Handles the response from the payment gateway after payment
	 */
	public function response()
	{
		$config  = $this->get('cfg')->checkout->payment;
		$id      = $this->get('request')->get('VPSTxId');
		$gateway = $this->get('commerce.gateway');
		$gateway->setUsername($config->username);
		$gateway->getGateway()->setTestMode((bool) $config->useTestPayments);

		try {

			$data = $gateway->handleResponse($id);

			if (!$data) {
				throw new \Exception('Order data could not be retreived');
			}

			$final = $gateway->completePurchase($data);

			if ($reference = $final->getTransactionReference()) {
				$paymentMethod = $this->get('order.payment.methods')->get('card');

				// Build the payment and add it ot the order
				$payment            = new \Message\Mothership\Commerce\Order\Entity\Payment\Payment;
				$payment->method    = $paymentMethod;
				$payment->amount    = $data['order']->totalGross;
				$payment->order     = $data['order'];
				$payment->reference = $reference;

				$data['order']->payments->append($payment);

				$order = $this->get('order.create')
					->setUser($data['order']->user)
					->create($data['order']);

				$salt  = $this->_services['cfg']['checkout']->payment->salt;
				$final->confirm($this->getUrl().$this->generateUrl('ms.ecom.checkout.payment.successful', array(
					'orderID' => $order->id,
					'hash' => $this->get('checkout.hash')->encrypt($order->id, $salt),
				)));

			} else {
				throw new \Exception('Payment was unsuccessful');
			}

		} catch (\Exception $e) {
			return $this->redirectToRoute('ms.ecom.checkout.payment.unsuccessful');
		}
	}

	public function unsuccessful()
	{
		return $this->render('Message:Mothership:Ecommerce::checkout:stage-4-error');
	}

	/**
	 * Load the order for the order confirmation page
	 *
	 * @param  int 		$orderID 	confirmed orderID to laod and display
	 * @param  string 	$hash   	hash to ensure we only display the order page to good people
	 *
	 * @return View 				order confirmation page
	 */
	public function successful($orderID, $hash)
	{
		// Get the salt and generate a new hash based on the given order number
		$salt = $this->_services['cfg']['checkout']->payment->salt;
		$generatedHash = $this->get('checkout.hash')->encrypt($orderID, $salt);

		// Check that the generated hash and the passed through hashes match
		if ($hash != $generatedHash) {
			throw new \Exception('Order hash doesn\'t match');
		}

		$this->get('event.dispatcher')->dispatch(
			\Message\Mothership\Ecommerce\Event::EMPTY_BASKET,
			new Event
		);
		$this->get('http.session')->remove('basket.order');

		// Get the order
		$order = $this->get('order.loader')->getByID($orderID);
		// Get the display name
		$shippingName = $this->get('shipping.methods')->get($order->shippingName)->getDisplayName();
		$siteName = $this->get('cfg')->app->name;

		return $this->render('Message:Mothership:Ecommerce::checkout:stage-4-success', array(
			'order' => $order,
			'items' => $order->items->getRows(),
			'shippingName' => $shippingName,
			'siteName'	=> $siteName,
		));
	}

	/**
	 * Handle local payments for testing on local envirnments
	 * This just bypasses the payment gateway but still creates an order
	 */
	public function zeroPayment($reference = '', $local = false)
	{
		// Get the order
		$order = $this->get('basket')->getOrder();

		// If this is a local payment and there is still outstanding payments
		// create a payment for the remaining amount and add it to the order
		if ($local && $order->getAmountDue() != $order->totalGross) {
			// Set the payment type as manual for now for local payments
			$paymentMethod = $this->get('order.payment.methods')->get('manual');
			// Add the payment to the basket order
			$this->get('basket')->addPayment($paymentMethod, $order->getAmountDue(), $reference);
		}

		// Save the order
		$order = $this->get('order.create')->create($this->get('basket')->getOrder());
		// Clear the basket
		$this->get('http.session')->remove('basket.order');
		// Get the salt
		$salt  = $this->_services['cfg']['checkout']->payment->salt;
		// Generate a hash and set the redirect url
		return $this->redirectToRoute('ms.ecom.checkout.payment.successful', array(
			'orderID' => $order->id,
			'hash' => $this->get('checkout.hash')->encrypt($order->id, $salt)
		));
	}

	public function getUrl()
	{
		$http = $this->get('request')->server->get('HTTPS') ? 'https://' : 'http://';

		return $http.$this->get('request')->server->get('HTTP_HOST');
	}
}
