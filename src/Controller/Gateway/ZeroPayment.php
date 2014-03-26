<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

use Message\Mothership\Commerce\Payable\PayableInterface;

class ZeroPayment extends Controller implements PurchaseControllerInterface, RefundControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function purchase(PayableInterface $payable, array $options = null)
	{
		/*
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
		*/
	}

	/**
	 * {@inheritDoc}
	 */
	public function refund(PayableInterface $refund, array $options = null)
	{

	}
}