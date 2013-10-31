<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Mothership\Ecommerce\Form\UserDetails;
use Message\Cog\Controller\Controller;
use Message\User\User;
use Message\User\AnonymousUser;

/**
 * Class Checkout/FinalCheck
 */
class FinalCheck extends Controller
{
	protected $_showForm = true;

	public function index()
	{
		$form = $this->deliveryMethodForm();
		$shippingName = $this->get('basket')->getOrder()->shippingName;
		$shippingDisplayName = $shippingName ? $this->get('shipping.methods')->get($shippingName)->getDisplayName() : '';
		return $this->render('Message:Mothership:Ecommerce::checkout:stage-2-final-check', array(
			'form'           => $form,
			'showForm'       => $this->_showForm,
			'shippingMethod' => $shippingDisplayName,
			'basket'         => $this->getGroupedBasket(),
			'order'          => $this->get('basket')->getOrder(),
		));
	}

	public function process()
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

	public function deliveryMethodForm()
	{
		$basket = $this->get('basket')->getOrder();

		$form = $this->get('form');
		$form->setName('shipping')
			->setAction($this->generateUrl('ms.ecom.checkout.confirm.action'))
			->setMethod('post')
			->setDefaultValues(array(
				'option' => $basket->shippingName
			)
		);

		$options = $this->get('shipping.methods')->getForOrder($basket);

		$filteredMethods = array();
		foreach ($options as $name => $option) {
			$filteredMethods[$name] = $option->getDisplayName().' £'. $option->getPrice($basket);
		}

		if (count($filteredMethods) == 1) {
			$shippingOption = $this->get('shipping.methods')->get(key($filteredMethods));
			$this->get('basket')->setShipping($shippingOption);

			$this->_showForm = false;
		}

		$form->add('option', 'choice', 'Delivery', array(
			'choices' => $filteredMethods,
		));

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

}
