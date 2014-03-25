<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

use InvalidRequestException;
use Message\Cog\Controller\Controller;
use Omnipay\SagePay\Message\Response as SagePayResponse;
use Message\Mothership\Commerce\Payable\PayableInterface;
use Message\Mothership\Ecommerce\Gateway\Validation\InvalidPayableException;

/**
 * Controller for purchases and refunds using the SagePay server gateway
 * integration. After a successful purchase has been made it creates
 * an order from the basket.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class SagePay extends Controller implements GatewayControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function purchase(PayableInterface $payable, array $options = null)
	{
		try {
			$returnUrl = $this->generateUrl('ms.ecom.gateway.sagepay.callback');
			$response = $this->get('gateway.adapter.sagepay')->purchase($payable, $returnUrl);
		}
		catch (InvalidRequestException $e) {
			$this->addFlash('error', 'An error occurred while trying to direct
				you to SagePay, please try again later.');

			return $this->redirectToRoute('ms.ecom.checkout.unsuccessful');
		}
		catch (InvalidPayableException $e) {
			$errors = $e->getErrors();

			foreach ($errors as $error) {
				$this->addFlash('error', $error);
			}

			return $this->redirectToReferer();
		}

		if ($response->isSuccessful()) {
			$this->_confirm($payable, $response);
		}
		elseif ($response->isRedirect()) {
			$response->redirect();
		}

		$responseData = $response->getData();
		if ($this->_isResponseErrorPublic($response['StatusDetail'])) {
			$this->addFlash('error', $response['StatusDetail'])
		}
		else {
			$this->addFlash('error', 'An error occurred while trying to direct
				you to SagePay, please try again later.');
		}

		return $this->redirectToRoute('ms.ecom.checkout.unsuccessful');
	}

	/**
	 * Handle the callback from SagePay after purchase and redirect. This route
	 * is not seen by a customer, instead it is hit by SagePay when attempting
	 * to complete a purchase.
	 */
	public function callback()
	{
		$transactionID = $this->get('request')->get('VPSTxId');

		try {
			$response = $this->get('gateway.adapter.sagepay')->completePurchase($transactionID);
		}
		catch (InvalidRequestException $e) {
			// this should return a response with just 'Status=ERROR'
			return;
		}

		if ($response->isSuccessful()) {
			// Confirm the response success with SagePay
			$this->_confirm($payable, $response);
		}

		// this should return a response with just 'Status=ERROR'
		return;
	}

	protected function _confirm(PayableInterface $payable, SagePayResponse $response)
	{
		// Create the order
		$order = $this->get('gateway.order.create')
			->setOrder(/* WHERE SHOULD THIS ORDER COME FROM? */)
			->setPaymentMethod($this->get('order.payment.methods')->get('sagepay'))
			->setPaymentAmount($payable->amount)
			->setUser($this->get('user.current'))
			->create();

		if (! $order) {
			throw new SomeException; // is this required?
		}

		$salt = $this->get('cfg')->checkout->payment->salt;
		$confirmUrl = $this->generateUrl('ms.ecom.checkout.successful', [
			'orderID' => $order->id,
			'hash'    => $this->get('checkout.hash')->encrypt($order->id, $salt),
		]);

		// Send the confirmation to SagePay
		$response->confirm($confirmUrl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function refund(PayableInterface $refund, array $options = null)
	{
		try {
			$response = $this->get('gateway.adapter.sagepay')->refund(
				$options['transactionID'],
				$refund
			);
		}
		catch (InvalidRequestException $e) {
			// Log error

			// Add error flash message

			// Redirect to generic payment error route
			return $this->redirectToRoute('ms.ecom.checkout.unsuccessful');
		}

		if ($response->isSuccessful()) {
			// redirect to generic payment successful route
		}
		elseif ($response->isRedirect()) {
			$response->redirect();
		}

		// Log error

		// Add error flash message

		// Redirect to generic payment error route
		return $this->redirectToRoute('ms.ecom.checkout.unsuccessful');
	}

	/**
	 * Checks if the given error code is one that should be visible to the
	 * customer.
	 *
	 * @param  int     $error
	 * @return boolean
	 */
	protected function _isResponseErrorPublic($error)
	{
		// @TODO: Make this generic and not reliant on SagePay
		list($code, $message) = explode(' : ', $error);

		$public = array(
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

		return in_array($code, $public);
	}
}