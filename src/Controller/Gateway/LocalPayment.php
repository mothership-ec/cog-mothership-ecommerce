<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

use Message\Mothership\Commerce\Payable\PayableInterface;

class Local extends ZeroPayment
{
	/**
	 * {@inheritDoc}
	 */
	public function purchase(PayableInterface $payable, array $options = null)
	{
		// If there are still outstanding payments create a payment for the
		// remaining amount and add it to the order.
		if ($payable->getPayableAmount() != $payable->getPayableTotal()) {

			/*
			// Set the payment type as manual for now for local payments
			$paymentMethod = $this->get('order.payment.methods')->get('manual');
			// Add the payment to the basket order
			$this->get('basket')->addPayment($paymentMethod, $order->getAmountDue(), $reference);
			*/
		}

		return parent::purchase($payable, $options);
	}

	/**
	 * {@inheritDoc}
	 */
	public function refund(PayableInterface $refund, array $options = null)
	{
		return parent::refund($payable, $options);
	}
}