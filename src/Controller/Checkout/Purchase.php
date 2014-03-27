<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Cog\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Message\Mothership\Commerce\Payable\PayableInterface;
use Message\Mothership\Commerce\Order\Entity\Payment\Payment;
use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
use Message\Mothership\Ecommerce\Gateway\CompleteControllerInterface;

/**
 * Controller for completing a checkout purchase. Called by a gateway to
 * complete a payable and returns a json response with the generated
 * confirmation url to which the customer should be redirected.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Purchase extends Controller implements CompleteControllerInterface
{
	/**
	 * Complete a order purchase by creating an order and generating a
	 * confirmation url to redirect the customer towards.
	 *
	 * {@inheritDoc}
	 */
	public function complete(PayableInterface $payable, MethodInterface $method)
	{
		// Build the payment and add it to the order
		$payment            = new Payment;
		$payment->method    = $method;
		$payment->amount    = $order->totalGross;
		$payment->order     = $order;
		$payment->reference = $reference;

		$payable->payments->append($payment);

		// Create the order
		$payable = $this->get('order.create')
			->setUser($payable->user)
			->create($payable);

		// Generate a confirmation url
		$salt = $this->get('cfg')->checkout->payment->salt;
		$confirmUrl = $this->getUrl().$this->generateUrl('ms.ecom.checkout.payment.successful', array(
			'orderID' => $payable->id,
			'hash'    => $this->get('checkout.hash')->encrypt($payable->id, $salt),
		)));

		// Create json response with the confirmation url
		$response = new JsonResponse;
		$response->setData([
			'confirmUrl' => $confirmUrl,
		]);

		return $response;
	}
}