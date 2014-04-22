<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Cog\Event\Event;
use Message\Cog\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Message\Mothership\Commerce\Payable\PayableInterface;
use Message\Mothership\Ecommerce\Event as EcommerceEvent;
use Message\Mothership\Commerce\Order\Entity\Payment\Payment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
use Message\Mothership\Ecommerce\Controller\Gateway\CompleteControllerInterface;

/**
 * Controller for completing a checkout purchase. Called by a gateway to
 * complete a payable and returns a json response with the generated
 * confirmation url to which the customer should be redirected.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Complete extends Controller implements CompleteControllerInterface
{
	/**
	 * Complete a order purchase by creating an order and generating a
	 * confirmation url to redirect the customer towards.
	 *
	 * {@inheritDoc}
	 */
	public function success(PayableInterface $payable, $reference, MethodInterface $method)
	{
		// Build the payment and add it to the order
		$payment            = new Payment;
		$payment->method    = $method;
		$payment->amount    = $payable->getPayableAmount();
		$payment->reference = $reference;

		$payable->payments->append($payment);

		// Create the order
		$payable = $this->get('order.create')
			->setUser($payable->user)
			->create($payable);

		// Generate a success url
		$salt = $this->get('cfg')->payment->salt;
		$confirmation = $this->generateUrl('ms.ecom.checkout.payment.confirmation', array(
			'orderID' => $payable->id,
			'hash'    => $this->get('checkout.hash')->encrypt($payable->id, $salt),
		), UrlGeneratorInterface::ABSOLUTE_URL);

		// Create json response with the success url
		return new JsonResponse([
			'url' => $confirmation,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function cancel(PayableInterface $payable)
	{
		return $this->failure($payable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function failure(PayableInterface $payable)
	{
		return $this->redirectToRoute('ms.ecom.checkout.payment.error');
	}

	/**
	 * Show the error page for an unsuccessful order.
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function error()
	{
		return $this->render('Message:Mothership:Ecommerce::checkout:stage-4-error');
	}

	/**
	 * Show the order confirmation page.
	 *
	 * @param  int 		$orderID 	confirmed orderID to laod and display
	 * @param  string 	$hash   	hash to ensure we only display the order page to good people
	 * @return View 				order confirmation page
	 */
	public function confirmation($orderID, $hash)
	{
		// Get the salt and generate a new hash based on the given order number
		$salt = $this->get('cfg')->payment->salt;
		$generatedHash = $this->get('checkout.hash')->encrypt($orderID, $salt);

		// Check that the generated hash and the passed through hashes match
		if ($hash != $generatedHash) {
			throw new \Exception('Order hash doesn\'t match');
		}

		$this->get('event.dispatcher')->dispatch(
			EcommerceEvent::EMPTY_BASKET,
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
}