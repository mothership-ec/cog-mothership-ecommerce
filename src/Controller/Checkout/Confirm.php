<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Mothership\Commerce\Order\Entity\Note\Note;
use Message\Mothership\Ecommerce\Form\UserDetails;
use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Address\Address;

/**
 * Class Checkout/Confirm
 */
class Confirm extends Controller
{
	protected $_showDeliveryMethodForm = true;

	public function index()
	{
		$order = $this->get('basket')->getOrder();

		if (!$order->getAddress(Address::DELIVERY) || !$order->getAddress(Address::BILLING)) {
			return $this->redirectToRoute('ms.ecom.checkout');
		}

		if ($order->shippingName && !$this->get('shipping.methods')->get($order->shippingName)->isAvailable($order)) {
			$order->shippingName = null;
		}

		$deliveryForm = $this->deliveryMethodForm();
		$confirmForm = $this->createForm($this->get('checkout.form.confirm'), [
			'order' => $order,
		]);

		$shippingDisplayName = $order->shippingName ?
			$this->get('shipping.methods')->get($order->shippingName)->getDisplayName() :
			'';

		return $this->render('Message:Mothership:Ecommerce::checkout:stage-2-confirm', array(
			'continueForm'           => $this->continueForm($order),
			'deliveryMethodForm'     => $deliveryForm,
			'showDeliveryMethodForm' => $this->_showDeliveryMethodForm,
			'confirmForm'            => $confirmForm,
			'shippingMethod'         => $shippingDisplayName,
			'basket'                 => $this->getGroupedBasket(),
			'order'                  => $order,
			'gateways'               => $this->get('gateways'),
		));
	}

	/**
	 * Get the continue to payment form with optional note field.
	 * @deprecated This form does not support multiple payment gateways. It also uses the old style form instead of
	 *             Symfony Form's core
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
		$continueForm = $this->continueForm();
		$continueData = $continueForm->getFilteredData();

		$confirmForm = $this->createForm($this->get('checkout.form.confirm'));
		$confirmForm->handleRequest();
		$confirmData = $confirmForm->getData();

		if (!empty($confirmData)) {
			return $this->_processConfirmForm($confirmForm, $confirmData);
		} elseif (!empty($continueData)) {
			return $this->_processContinueForm($continueForm, $continueData);
		} else {
			throw new \LogicException('Neither checkout form submitted');
		}
	}

	private function _processConfirmForm($form, array $data)
	{
		$gateway = null;

		if ($form->isValid()) {
			foreach ($this->get('gateways') as $registeredGateway) {
				if ($form->get($registeredGateway->getName())->isClicked()) {
					$gateway = $registeredGateway;
					continue;
				}
			}

			if (null === $gateway) {
				throw new \LogicException('No submit button was clicked');
			}

		} else {
			$this->addFlash('error', 'ms.ecom.checkout.error.form');
			return $this->redirectToReferer();
		}

		return $this->_processConfirmData($gateway, $data);
	}

	private function _processContinueForm($form, array $data)
	{
		if (! $form->isValid()) {
			$this->addFlash('error', 'ms.ecom.checkout.error.form');

			return $this->redirectToReferer();
		}

		return $this->_processConfirmData($this->get('gateway'), $data);
	}

	private function _processConfirmData($gateway, $data)
	{
		// Add the note to the order if it is set, else clear out the notes.
		if (isset($data['note']) && !empty($data['note'])) {
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
			$this->addFlash('error', 'ms.ecom.checkout.error.shipping');

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

		// If the customer is being impersonated by an admin user, or if there
		// is no remaining payable amount, use the zero payment dummy gateway
		if ($impersonating || 0 == $this->get('basket')->getOrder()->getPayableAmount()) {
			$this->addFlash('success', 'ms.ecom.checkout.no-amount');
			$gateway = $this->get('gateway.adapter.zero-payment');
		}

		// Forward the request to the gateway purchase reference
		$controller = 'Message:Mothership:Ecommerce::Controller:Checkout:Complete';

		return $this->forward($gateway->getPurchaseControllerReference(), [
			'payable' => $this->get('basket')->getOrder(),
			'stages'  => [
				'cancel'       => $controller . '#cancel',
				'failure'      => $controller . '#failure',
				'success'      => $controller . '#success',
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
			->addOptions([
				'attr' => [
					'id' => 'delivery-method-form',
				],
			])
			->setDefaultValues(array(
					'option' => $basket->shippingName
				)
			);

		$options = $this->get('shipping.methods')->getForOrder($basket);

		$filteredMethods = [];
		foreach ($options as $name => $option) {
			$symbol = substr($this->get('currency_formatter')->formatCurrency(0, $basket->currencyID), 0, -4);
			$filteredMethods[$name] = $option->getDisplayName() . ' ' . $symbol . $option->getPrice();
		}

		if (null === $this->get('basket')->getOrder()->shippingName) {
			$shippingOption = $this->get('shipping.methods')->get(key($filteredMethods));
			$this->get('basket')->setShipping($shippingOption);
		}

		if (count($filteredMethods) == 1) {
			$this->_showDeliveryMethodForm = false;
		}

		$form->add('option', 'choice', 'Delivery', array(
			'choices' => $filteredMethods,
			'attr' => [
				'id' => 'delivery-method-options',
			],
		));

		return $form;
	}

	public function processDeliveryMethod()
	{
		$form = $this->deliveryMethodForm();
		if ($form->isValid() && $data = $form->getFilteredData()) {
			$basket = $this->get('basket');
			$shippingOption = $this->get('shipping.methods')->get($data['option']);

			if (!$shippingOption->isAvailable($basket->getOrder())) {
				throw new \LogicException('Shipping method `' . $shippingOption->getName() . '` is not available on this order');
			}

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
