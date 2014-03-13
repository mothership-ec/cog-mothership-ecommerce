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

		// If local payments is turned on, skip the payment
		if ($this->get('cfg')->checkout->payment->useLocalPayments) {
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

		// Ensure the addresses are complete
		$addressIncomplete = false;
		foreach (array($billing, $delivery) as $address) {
			foreach (array('forename', 'surname', 'town', 'postcode', 'countryID', 'telephone') as $property) {
				if (! property_exists($address, $property) or ! $address->$property) {
					$addressIncomplete = true;
				}
			}

			if (! property_exists($address, 'lines') or ! isset($address->lines[1])) {
				$addressIncomplete = true;
			}

			$states = $this->get('state.list')->all();
			if (isset($states[$address->countryID]) and
				(empty($address->stateID) or ! isset($states[$address->countryID][$address->stateID]))
			) {
				$addressIncomplete = true;
			}
		}

		// If any of the addresses are incomplete, warn the user and prevent
		// them from placing the payment.
		if ($addressIncomplete) {
			$this->addFlash('warning','Your addresses are incomplete, please enter the missing required information.');

			return $this->redirectToRoute('ms.ecom.checkout.details.addresses');
		}

		$gateway->setUsername($config->username);
		$gateway->getGateway()->setTestMode((bool) $config->useTestPayments);

		$gateway->setBillingAddress($billing);
		$gateway->setDeliveryAddress($delivery);
		$gateway->setOrder($order);
		$gateway->setPaymentAmount($order->getAmountDue(), $order->currencyID);
		$gateway->setRedirectUrl($this->getUrl().'/checkout/payment/response');

		$response = $gateway->send();
		$gateway->saveResponse();

		$responseData = $response->getData();

		$this->_logResponse($responseData);

		if ($response->isRedirect()) {
		    $response->redirect();
		} else {
			// @TODO: Make this generic and not reliant on SagePay
			if ($this->_isDisplayError($responseData['StatusDetail'])) {
				$this->addFlash('error', $responseData['StatusDetail']);
			}
			else {
				$this->addFlash('error', 'An error occurred when trying to connect to the payment gateway, please try
					again later or contact us if you continue to experience problems.');
			}
		}

		return $this->redirectToRoute('ms.ecom.checkout.confirm');
	}

	/**
	 * Handles the response from the payment gateway after payment
	 */
	public function response()
	{
		// If the transaction was cancelled by the user
		if ($this->get('request')->get('Status') == 'ABORT') {
			$this->addFlash('info', 'It seems you cancelled your payment, if this was unintentional please try again.');
			return $this->redirectToRoute('ms.ecom.checkout.confirm');
		}

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

	/**
	 * Log the response data.
	 *
	 * @param  array $responseData
	 * @return void
	 */
	protected function _logResponse($responseData)
	{
		// @TODO: Make this generic and not reliant on SagePay
		switch($responseData['Status']) {
			case 'OK':
				$this->get('log.payments')->info(
					"A connection to the payment gateway was made successfully.",
					$responseData
				);
				break;

			case 'OK REPEATED':
				$this->get('log.payments')->notice(
					"A connection to the payment gateway was repeated.",
					$responseData
				);
				break;

			case 'INVALID':
				$this->get('log.payments')->warning(
					"Some data sent to the payment gateway was invalid.",
					$responseData
				);
				break;

			case 'MALFORMED':
				$this->get('log.payments')->alert(
					"The data sent to the payment gateway was malformed.",
					$responseData
				);
				break;

			case 'ERROR':
				$this->get('log.payments')->alert(
					"An error occurred at the payment gateway.",
					$responseData
				);
				break;
		}
	}

	/**
	 * Determine if an error message should be displayed to the customer.
	 *
	 * @param  string  $error Error message from payment gateway
	 * @return boolean
	 */
	protected function _isDisplayError($error)
	{
		// @TODO: Make this generic and not reliant on SagePay
		list($code, $message) = explode(' : ', $error);

		$display = array(
			3024,  // The ContactNumber is too long.
			3025,  // The DeliveryPostCode is too long.
			3026,  // The DeliveryAddress is too long.
			3027,  // The BillingPostCode is too long.
			3028,  // The BillingAddress is too long.
			3078,  // The CustomerEMail format is invalid.
			3108,  // The BillingSurname is too long.
			5038,  // The Delivery Phone contains invalid characters.
			5039,  // The Delivery City contains invalid characters.
			5040,  // The Billing Surname contains invalid characters.
			5041,  // The Billing Firstname contains invalid characters.
			5042,  // The Billing Address1 contains invalid characters.
			5043,  // The Billing Address2 contains invalid characters.
			5044,  // The Billing City contains invalid characters.
			5045,  // The Billing Phone contains invalid characters.
			5046,  // The Delivery Surname contains invalid characters.
			5047,  // The Delivery Firstname contains invalid characters.
			5048,  // The Delivery Address1 contains invalid characters.
			5049,  // The Delivery Address2 contains invalid characters.
			5050,  // The Billing Address contains invalid characters.
			5051,  // The Contact Number contains invalid characters.
			5052,  // The Customer Name contains invalid characters.
			5053,  // The Email Message contains invalid characters.
			5054,  // The Cardholder Name contains invalid characters.
			5055,  // A Postcode field contains invalid characters.
			11005, // There was error processing the payment at the bank site.
			11006, // The transaction was declined.
			11007, // The transaction was declined by the bank.
			10029, // The CardNumber field should only contain numbers. No spaces, hyphens or other characters or separators.
		);

		return in_array($code, $display);
	}
}
