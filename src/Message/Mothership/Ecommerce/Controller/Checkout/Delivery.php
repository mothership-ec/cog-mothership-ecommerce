<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Mothership\Ecommerce\Form\UserDetails;
use Message\Cog\Controller\Controller;
use Message\User\User;
use Message\User\AnonymousUser;
/**
 * Class Checkout/Delivery
 */
class Delivery extends Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:Ecommerce::Checkout:delivery', array(
			'form'    => $this->deliveryMethodForm(),
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
			->setAction($this->generateUrl('ms.ecom.checkout.delivery.action'))
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

		$form->add('option', 'choice', 'Delivery method', array(
			'choices' => $filteredMethods,
		));

		return $form;
	}

}
