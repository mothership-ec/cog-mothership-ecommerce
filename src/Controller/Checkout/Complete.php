<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Mothership\Ecommerce\Event as EcommerceEvent;
use Message\Mothership\Ecommerce\Controller\Gateway\CompleteControllerInterface;
use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Payment\Payment;
use Message\Mothership\Commerce\Payment\MethodInterface;
use Message\Mothership\Commerce\Payable\PayableInterface;
use Message\Mothership\Commerce\Order\Entity\Payment\Payment as OrderPayment;
use Message\Cog\Controller\Controller;
use Message\Cog\Event\Event;
use Message\Cog\HTTP\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * Controller for completing a checkout purchase. Handles success, cancel and
 * failure results for a payable.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Complete extends Controller implements CompleteControllerInterface
{
	/**
	 * Complete a order purchase by creating an order and generating a
	 * success url to redirect the customer towards.
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

		$payable->payments->append(new OrderPayment($payment));

		// Create the order
		$payable = $this->get('order.create')
			->setUser($payable->user)
			->create($payable);

		// Generate a success url
		$salt = $this->get('cfg')->payment->salt;
		$successful = $this->generateUrl('ms.ecom.checkout.payment.successful', array(
			'orderID' => $payable->id,
			'hash'    => $this->get('checkout.hash')->encrypt($payable->id, $salt),
		), UrlGeneratorInterface::ABSOLUTE_URL);

		// Save gateway against payment
		$gateway = $this->get('http.session')->get('gateway.current');

		// If gateway is not set on session, get default gateway
		if (null === $gateway) {
			$gatewayNames = $this->get('cfg')->payment->gateway;
			$gatewayName = is_array($gatewayNames) ? array_shift($gatewayNames) : $gatewayNames;
			$gateway = $this->get('gateway.collection')->get($gatewayName);
		}

		$this->get('payment.gateway.edit')->save($payment, $gateway);

		// Create json response with the success url
		return new JsonResponse([
			'url' => $successful,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function cancel(PayableInterface $payable)
	{
		return $this->redirectToRoute('ms.ecom.checkout.confirm');
	}

	/**
	 * {@inheritDoc}
	 */
	public function failure(PayableInterface $payable)
	{
		return $this->redirectToRoute('ms.ecom.checkout.payment.unsuccessful');
	}

	/**
	 * Show the error page for an unsuccessful order.
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function unsuccessful()
	{
		return $this->render('Message:Mothership:Ecommerce::checkout:stage-4-error');
	}

	/**
	 * Show the confirmation page for a successful order.
	 *
	 * @param int $orderID                     Confirmed orderID to laod and display
	 * @param string $hash                     Hash to ensure we only display the order page to good people
	 * @throws \Exception
	 *
	 * @return \Message\Cog\HTTP\Response      Order confirmation page
	 */
	public function successful($orderID, $hash)
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

		$this->get('event.dispatcher')->dispatch(
			EcommerceEvent::ORDER_SUCCESS,
			new Order\Event\Event($order)
		);

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