<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

use Message\Mothership\Commerce\...\PayableInterface;

class SagePay
{
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

	public function callback()
	{
		$transactionID = $this->get('request')->get('VPSTxId');

		try {
			$this->get('gateway.adapter.sagepay')->completePurchase($transactionID);
		}
		catch () {

		}

		if ($response->isSuccessful()) {

		}
	}
}