<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

use Message\Mothership\Commerce\...\PayableInterface;

/**
 * Controller for payments using the SagePay server gateway integration.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class SagePay
{
	/**
	 * Purchase a payable.
	 *
	 * @param  PayableInterface $payable
	 */
	public function purchase(PayableInterface $payable)
	{
		try {
			$response = $this->get('gateway.adapter.sagepay')->purchase($payable);
		}
		catch (InvalidRequestException $e) {
			// redirect to generic payment error route
			// log error
		}

		if ($response->isSuccessful()) {
			// redirect to generic payment successful route
		}
		elseif ($response->isRedirect()) {
			$response->redirect();
		}

		// redirect to generic payment error route
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
			// redirect to generic payment error route
			// log error
		}

		if ($response->isSuccessful()) {
			$response->confirm(success url);
		}

		// redirect to generic payment error route
	}

	/**
	 * Refund a payable.
	 *
	 * @param  PayableInterface $payable
	 */
	public function refund(PayableInterface $payable)
	{
		try {
			$response = $this->get('gateway.adapter.sagepay')->purchase($payable);
		}
		catch (InvalidRequestException $e) {
			// redirect to generic payment error route
			// log error
		}

		if ($response->isSuccessful()) {
			// redirect to generic payment successful route
		}
		elseif ($response->isRedirect()) {
			$response->redirect();
		}

		// redirect to generic payment error route
	}
}