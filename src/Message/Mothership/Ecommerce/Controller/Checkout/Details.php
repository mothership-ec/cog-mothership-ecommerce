<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Mothership\Ecommerce\Form\UserDetails;
use Message\Cog\Controller\Controller;
use Message\User\User;
use Message\User\AnonymousUser;
/**
 * Class Checkout
 * @package Message\Mothership\Ecommerce\Controller\Fulfillment
 *
 * Controller for processing orders in Fulfillment
 */
class Details extends Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:Ecommerce::Checkout:details', array(
			'order'    => $this->get('basket')->getOrder(),
		));
	}

	public function addresses()
	{
		$billing  = $this->addressForm('billing', $this->generateUrl('ms.ecom.checkout.details.addresses.action', array('type' => 'billing')));
		$delivery = $this->addressForm('delivery', $this->generateUrl('ms.ecom.checkout.details.addresses.action', array('type' => 'delivery')));

		return $this->render('Message:Mothership:Ecommerce::Checkout:details-addresses', array(
			'billing'    => $billing,
			'delivery'	 => $delivery,
		));
	}

	public function addressProcess($type)
	{

		$form = $this->addressForm($type, $this->generateUrl('ms.ecom.checkout.details.addresses.action', array('type' => $type)));

		if ($form->isValid() && $data = $form->getFilteredData()) {

			$address            = new \Message\Mothership\Commerce\Order\Entity\Address\Address;
			$address->type      = $type;
			$address->id        = $type;
			$address->lines[1]  = $data['address_line_1'];
			$address->lines[2]  = $data['address_line_2'];
			$address->lines[3]  = $data['address_line_3'];
			$address->lines[4]  = $data['address_line_4'];
			$address->town      = $data['town'];
			$address->postcode  = $data['postcode'];
			$address->country   = $this->get('country.list')->getByID($data['country_id']);
			$address->countryID = $data['country_id'];
			$address->order     = $this->get('basket')->getOrder();
			$address->forename  = $data['forename'];
			$address->surname   = $data['surname'];

			$this->get('basket')->addAddress($address);

			$this->addFlash('success', 'Address updated successfully');

		}

		return $this->redirectToReferer();

	}

	public function addressForm($type = 'billing', $action)
	{
		$address = array_pop($this->get('basket')
			->getOrder()
			->addresses
			->getByProperty('type', $type));

		$form = new UserDetails($this->_services);
		$form = $form->buildForm($this->get('user.current'), $address, $type, $action);

		return $form;
	}

}
