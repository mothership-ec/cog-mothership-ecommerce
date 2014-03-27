<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Mothership\Commerce\Order\Entity\Note\Note;
use Message\Mothership\Ecommerce\Form\UserDetails;
use Message\Cog\Controller\Controller;
use Message\User\User;
use Message\User\AnonymousUser;

/**
 * Class Checkout/FinalCheck
 */
class FinalCheck extends Controller
{
	protected $_showDeliveryMethodForm = true;

	public function index()
	{
		// Get the delivery form, before checking for the shipping method name
		// otherwise this might not be set yet when there is only one method
		// available for the order.
		$deliveryForm = $this->deliveryMethodForm();

		$shippingName = $this->get('basket')->getOrder()->shippingName;
		$shippingDisplayName = $shippingName ? $this->get('shipping.methods')->get($shippingName)->getDisplayName() : '';

		$order = $this->get('basket')->getOrder();

		return $this->render('Message:Mothership:Ecommerce::checkout:stage-2-final-check', array(
			'continueForm'           => $this->continueForm($order),
			'deliveryMethodForm'     => $deliveryForm,
			'showDeliveryMethodForm' => $this->_showDeliveryMethodForm,
			'shippingMethod'         => $shippingDisplayName,
			'basket'                 => $this->getGroupedBasket(),
			'order'                  => $order,
		));
	}

	/**
	 * Get the continue to payment form with optional note field.
	 *
	 * @return \Message\Cog\Form\Handler
	 */
	public function continueForm($order = null)
	{
		$form = $this->get('form');
		$form->setName('continue')
			->setAction($this->generateUrl('ms.ecom.checkout.confirm.action'));

		$form->add('note', 'textarea', 'Please add any additional comments you may have regarding your order or delivery', array(
			'data' => ($order and $order->notes->count()) ? $order->notes[0]->note : '',
		))->val()->optional();

		return $form;
	}

	/**
	 * Process the continue to payment form, storing the note against the
	 * order.
	 *
	 * @return \Message\Cog\HTTP\RedirectResponse Referer
	 */
	public function processContinue()
	{
		$form = $this->continueForm();

		// Check the form and data are valid
		if (! $form->isValid() or false === $data = $form->getFilteredData()) {
			$this->addFlash('error', 'An error occurred, please try again');

			return $this->redirectToReferer();
		}

		// Add the note to the order if it is set, else clear out the notes.
		if (isset($data['note']) and ! empty($data['note'])) {
			$note = new Note;
			$note->note = $data['note'];
			$note->raisedFrom = 'checkout';
			$note->customerNotified = false;

			$this->get('basket')->setEntities('notes', array($note));
		}
		else {
			$this->get('basket')->getOrder()->notes->clear();
		}

		// Ensure a delivery method has been chosen
		if (! $this->get('basket')->getOrder()->shippingName) {
			$this->addFlash('error', 'You must select a delivery method before continuing.');

			return $this->redirectToRoute('ms.ecom.checkout.confirm');
		}

		// Check if the customer is being impersonated by an admin user
		$impersonateID   = $this->get('http.session')->get('impersonate.impersonateID');
		$impersonateData = (array) $this->get('http.session')->get('impersonate.data');
		$impersonating   = (
			array_key_exists('order_skip_payment', $impersonateData) and
			$impersonateData['order_skip_payment'] and
			$impersonateID == $this->get('user.current')->id
		);

		// If the customer is being impersonated by an admin user, use the local
		// payment gateway
		if ($impersonating) {
			$gateway = $this->get('gateway.adapter.local-payment');
		}
		// If there is no remaining payable amount, use the zero payment dummy
		// gateway to create the order
		elseif ($this->get('basket')->getOrder()->getPayableAmount() == 0) {
			$gateway = $this->get('gateway.adapter.zero-payment');
		}
		// Otherwise use the default gateway
		else {
			$gateway = $this->get('gateway');
		}

		// Forward the request to the gateway purchase reference
		return $this->forward($gateway->getPurchaseControllerReference(), [
			'payable' => $this->get('basket')->getOrder(),
			'stages'  => [
				'cancelRoute'       => 'ms.ecom.checkout.unsuccessful',
				'failureRoute'      => 'ms.ecom.checkout.unsuccessful',
				'successRoute'      => 'ms.ecom.checkout.successful',
				'completeReference' => 'Message:Mothership:Ecommerce::Controller:Checkout:Complete#complete'
			],
		]);
	}

	public function deliveryMethodForm()
	{
		$basket = $this->get('basket')->getOrder();

		$form = $this->get('form');
		$form->setName('shipping')
			->setAction($this->generateUrl('ms.ecom.checkout.confirm.delivery.action'))
			->setMethod('post')
			->setDefaultValues(array(
				'option' => $basket->shippingName
			)
		);

		$options = $this->get('shipping.methods')->getForOrder($basket);

		$filteredMethods = array();
		foreach ($options as $name => $option) {
			$filteredMethods[$name] = $option->getDisplayName().' £'. $option->getPrice();
		}

		if (count($filteredMethods) == 1) {
			$shippingOption = $this->get('shipping.methods')->get(key($filteredMethods));
			$this->get('basket')->setShipping($shippingOption);

			$this->_showDeliveryMethodForm = false;
		}

		$form->add('option', 'choice', 'Delivery', array(
			'choices' => $filteredMethods,
		));

		return $form;
	}

	public function processDeliveryMethod()
	{
		$form = $this->deliveryMethodForm();
		if ($form->isValid() && $data = $form->getFilteredData()) {
			$basket = $this->get('basket');
			$shippingOption = $this->get('shipping.methods')->get($data['option']);
			$basket->setShipping($shippingOption);
			$this->addFlash('success', 'Shipping option saved');
		}

		return $this->redirectToReferer();
	}

	public function getGroupedBasket()
	{
		$basketDisplay = array();
		foreach ($this->get('basket')->getOrder()->items as $item) {

			if (!isset($basketDisplay[$item->unitID]['quantity'])) {
				$basketDisplay[$item->unitID]['quantity'] = 0;
			}

			if (!isset($basketDisplay[$item->unitID]['subTotal'])) {
				$basketDisplay[$item->unitID]['subTotal'] = 0;
			}

			$basketDisplay[$item->unitID]['item'] = $item;
			$basketDisplay[$item->unitID]['quantity'] += 1;
			$basketDisplay[$item->unitID]['subTotal'] += $item->gross;
		}

		return $basketDisplay;
	}

}
