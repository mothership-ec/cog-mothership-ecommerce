<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Cog\Controller\Controller;

use Message\Mothership\Commerce\Order\Entity\Note\Note;

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
				$unit = $product->units->get($unitID);
				$basket->updateQuantity($unit, $quantity);
			}

			$this->addFlash('success','Basket updated');
		}

		return $this->_renderCheckout($form);
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

	/**
	 * Get the add note form.
	 *
	 * @return \Message\Cog\Form\Handler
	 */
	public function noteForm()
	{
		$form = $this->get('form');
		$form->setName('add_note');

		$form->add('note', 'textarea', 'Note');

		return $form;
	}

	/**
	 * Store a note against the basket.
	 *
	 * @return \Message\Cog\HTTP\RedirectResponse Referer
	 */
	public function processNote()
	{
		$form = $this->noteForm();

		if ($form->isValid() and $data = $form->getFilteredData()) {
			$note = new Note;
			$note->note = $data['note'];
			$note->raisedFrom = 'checkout';
			$note->customerNotified = false;

			$this->get('basket')->addNote($note);

			$this->addFlash('success', 'Your note was added to order');
		}
		else {
			$this->addFlash('error', 'Could not add note, message was invalid');
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

	protected function _renderCheckout($checkoutForm)
	{
		return $this->render('Message:Mothership:Ecommerce::checkout:stage-1-review', array(
			'basket'   => $this->getGroupedBasket(),
			'order'    => $this->get('basket')->getOrder(),
			'form'     => $checkoutForm,
			'noteForm' => $this->noteForm(),
		));
	}
}