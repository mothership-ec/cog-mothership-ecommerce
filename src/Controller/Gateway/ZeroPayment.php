<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

use Message\Mothership\Commerce\Payable\PayableInterface;

class ZeroPayment extends Controller implements PurchaseControllerInterface, RefundControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function purchase(PayableInterface $payable, array $stages, array $options = null)
	{
		// Forward to the method for completing the payable and capture the
		// response containing the confirm url
		$completeResponse = $this->forward($stages['completeReference'], [
			'payable' => $payable,
			'method'  => $this->get('order.payment.methods')->get('manual'),
		]);

		$completeData = json_decode($completeResponse->getContent());

		return $this->redirect($completeData['successUrl']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function refund(PayableInterface $refund, array $stages, array $options = null)
	{

	}
}