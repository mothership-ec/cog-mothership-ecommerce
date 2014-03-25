<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\...\PayableInterface;
use Omnipay\SagePay\Message\Response as SagePayResponse;
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
			// Log error

			// Add error flash message
			$this->addFlash('error', 'An error occurred while trying to direct
				you to SagePay, please try again later.');

			// Redirect to generic payment error route
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
			// Confirm the response success with SagePay
			$this->_confirm($payable, $response);
		}
		elseif ($response->isRedirect()) {
			// Redirect user to external payment service
			$response->redirect();
		}

		// Log error

		// Add error flash message
		$this->addFlash('error', 'An error occurred while trying to direct you
			to SagePay, please try again later.');

		// Redirect to generic payment error route
		return $this->redirectToRoute('ms.ecom.checkout.unsuccessful');
	}

	/**
	 * Handle the callback from SagePay after purchase and redirect.
	 */
	public function callback()
	{
		$transactionID = $this->get('request')->get('VPSTxId');

		try {
			$response = $this->get('gateway.adapter.sagepay')->completePurchase($transactionID);
		}
		catch (InvalidRequestException $e) {
			// Log error

			// Add error flash message

			// Redirect to generic payment error route
			return $this->redirectToRoute('ms.ecom.checkout.unsuccessful');
		}

		if ($response->isSuccessful()) {
			// Confirm the response success with SagePay
			$this->_confirm($payable, $response);
		}

		// Log error

		// Add error flash message

		// Redirect to generic payment error route
		return $this->redirectToRoute('ms.ecom.checkout.unsuccessful');
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
}