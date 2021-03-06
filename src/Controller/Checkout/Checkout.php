<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\Checkout\Event as CheckoutEvent;
use Message\Mothership\Ecommerce\Checkout\Events as CheckoutEvents;

/**
 * Class Checkout
 * @package Message\Mothership\Ecommerce\Controller\Fulfillment
 *
 * Controller for processing orders in Fulfillment
 */
class Checkout extends Controller
{
	public function index()
	{
		return $this->_renderCheckout($this->checkoutForm());
	}

	public function process()
	{
		$basket = $this->get('basket');
		$form = $this->checkoutForm();
		if ($form->isValid() && $data = $form->getFilteredData()) {
			foreach($data['items'] as $unitID => $quantity) {
				$product = $this->get('product.loader')->getByUnitID($unitID);
				$unit = $product->getUnit($unitID);
				$basket->updateQuantity($unit, $quantity);
			}

			$this->get('event.dispatcher')->dispatch(
				CheckoutEvents::REVIEW,
				new CheckoutEvent($this->get('basket')->getOrder(), $data)
			);

			$this->addFlash('success','Basket updated');
		}

		// recreate the form so the correct quantities are used
		return $this->_renderCheckout($this->checkoutForm());
	}

	public function removeUnit($unitID)
	{
		$basket = $this->get('basket');
		$unit   = $this->get('product.unit.loader')->getByID($unitID);

		$basket->updateQuantity($unit, 0);

		$this->addFlash('success','The item was successfully removed.');

		return $this->redirectToReferer();
	}

	public function checkoutForm()
	{
		$form = $this->get('form');
		$form->setName('confirm_basket')
			->setAction($this->generateUrl('ms.ecom.checkout.action'))
			->setMethod('post');

		$basketDisplay = $this->getGroupedBasket();

		$itemsForm = $this->get('form')
			->setName('items')
			->addOptions(array(
				'auto_initialize' => false,
			)
		);

		foreach ($basketDisplay as $item) {
			$itemsForm->add(
				(string) $item['item']->unitID,
				'number',
				sprintf('Quantity for %s, %s', $item['item']->productName, $item['item']->options),
				array(
					'data' => $item['quantity'],
				))
			->val()
			->number();
		}

		$form->add($itemsForm, 'form');

		return $form;
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

	protected function _renderCheckout($checkoutForm)
	{
		return $this->render('Message:Mothership:Ecommerce::checkout:stage-1-review', array(
			'basket'   => $this->getGroupedBasket(),
			'order'    => $this->get('basket')->getOrder(),
			'form'     => $checkoutForm,
		));
	}
}