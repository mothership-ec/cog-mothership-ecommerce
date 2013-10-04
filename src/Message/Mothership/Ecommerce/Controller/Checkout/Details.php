<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Mothership\Ecommerce\Form\UserDetails;
use Message\Cog\Controller\Controller;
use Message\User\User;
use Message\User\AnonymousUser;

/**
 * Checkout Details - amend order addresses
 */
class Details extends Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:Ecommerce::checkout:stage-1a-login-register', array(
			'order'    => $this->get('basket')->getOrder(),
		));
	}

	public function register()
	{
		return $this->render('Message:Mothership:Ecommerce::checkout:stage-1c-register', array(
			'form' => $this->registerForm(),
		));
	}

	public function registerForm($action = '', $types = array())
	{
		$action = $action ?: $this->generateUrl('ms.ecom.checkout.details.register.process');

		$form = $this->getFullAddressForm($action, $types);
		$form->add('email','email','Email Address');
		$form->add('password','password','Password');
		$form->add('password_check','password','Password again');

		return $form;
	}

	/**
	 * Vaidating and adding new user and adding addresses to the order
	 */
	public function registerProcess()
	{
		$action = $this->generateUrl('ms.ecom.checkout.details.register.process');
		// Don't get this from the form just yet
		$data = $this->get('request')->request->get('register');

		// If we are delivering to the billing address we only need to validate
		if (isset($data['deliver_to_billing']) && $data['deliver_to_billing']) {
			// Don't validate the delivery address
			$form = $this->registerForm($action, array('billing'));
		} else {
			$form = $this->registerForm($action);
		}

		$data = $form->getFilteredData();

		// Validate the full form input
		if (!$form->isValid()) {
			$this->addFlash('error', 'Please ensure all required fields have been completed');

			return $this->redirectToReferer();
		}

		// Check email and passwords have been supplied
		if (empty($data['email']) || empty($data['password']) || empty($data['password_check'])) {
			$this->addFlash('error', 'Please ensure all required fields have been completed');

			return $this->redirectToReferer();
		}
		// Check passwords match
		if ($data['password'] != $data['password_check']) {
			$this->addFlash('error', 'Please ensure your passwords match');

			return $this->redirectToReferer();
		}

		// Build and create the user
		$user = $this->get('user');
		$user->forename = $data['billing']['forename'];
		$user->surname = $data['billing']['surname'];
		$user->password = $data['password'];
		$user->email = $data['email'];
		$user->title = $data['billing']['title'];

		try {
			$user = $this->get('user.create')->save($user);
		} catch (Exception $e) {
			$this->addFlash('error', 'Email address is already in use');

			return $this->redirectToReferer();
		}

		// Set the user session
		$this->get('http.session')->set($this->get('cfg')->user->sessionName, $user);

		// Fire the user login event
		$this->get('event.dispatcher')->dispatch(
			\Message\User\Event\Event::LOGIN,
			new \Message\User\Event\Event($user)
		);

		foreach (array('delivery','billing') as $type) {

			$address            = new \Message\Mothership\Commerce\Order\Entity\Address\Address;
			$address->type      = $type;
			$address->id        = $type;

			if ($type == 'delivery' && isset($data['deliver_to_billing']) && $data['deliver_to_billing']) {
				$type = 'billing';
			}

			$address->lines[1]  = $data[$type]['address_line_1'];
			$address->lines[2]  = $data[$type]['address_line_2'];
			$address->lines[3]  = $data[$type]['address_line_3'];
			$address->lines[4]  = null;
			$address->town      = $data[$type]['town'];
			$address->postcode  = $data[$type]['postcode'];
			$address->country   = $this->get('country.list')->getByID($data[$type]['country_id']);
			$address->countryID = $data[$type]['country_id'];
			$address->order     = $this->get('basket')->getOrder();
			$address->forename  = $data[$type]['forename'];
			$address->surname   = $data[$type]['surname'];

			$this->get('basket')->addAddress($address);
		}

		return $this->redirectToRoute('ms.ecom.checkout.confirm');
	}

	/**
	 * Output for the editing of addresses
	 */
	public function addresses()
	{
		$form = $this->getFullAddressForm($this->generateUrl('ms.ecom.checkout.details.addresses.action'));

		return $this->render('Message:Mothership:Ecommerce::checkout:stage-1b-change-addresses', array(
			'form' => $form
		));
	}

	/**
	 * Returns a form of the billing and delivery addresses and also a deliver
	 * to billing checkbox
	 *
	 * @param  string  		$action action url
	 * @param  array   		$types  array of address types
	 *
	 * @return FormHandler  form
	 */
	public function getFullAddressForm($action, $types = array())
	{
		$form = $this->get('form');
		$form->setMethod('POST')
			->setName('register')
			->setAction($action);

		if (!$types) {
			$types = array(
				'billing',
				'delivery',
			);
		}

		foreach ($types as $type) {
			$typeForm = $this->addressForm($type, $this->generateUrl('ms.ecom.checkout.details.register.process'));
			$form->add($typeForm,'form');
		}

		$deliverToBilling = array();
		if ($this->get('basket')->getOrder()->getAddress('delivery') ==
			$this->get('basket')->getOrder()->getAddress('billing')
		) {
			$deliverToBilling = array(
				'checked' => $deliverToBilling
			);
		}

		$form->add('deliver_to_billing','checkbox', 'Deliver to Billing address', array(
			'attr' => $deliverToBilling))->val()->optional();

		return $form;
	}

	public function addressProcess()
	{
		$action = $this->generateUrl('ms.ecom.checkout.details.addresses.action');
		$form = $this->getFullAddressForm($action);
		$data = $form->getFilteredData();

		// If we are delivering to the billing address we only need to validate
		if (isset($data['deliver_to_billing']) && $data['deliver_to_billing']) {
			// Don't validate the delivery address
			$form = $this->getFullAddressForm($action, array('billing'));
		}

		// Validate the full form input
		if (!$form->isValid()) {
			$this->addFlash('error', 'Please ensure all required fields have been completed');

			return $this->redirectToReferer();
		}

		foreach (array('delivery','billing') as $type) {
			$address            = new \Message\Mothership\Commerce\Order\Entity\Address\Address;
			$address->type      = $type;
			$address->id        = $type;

			if ($type == 'delivery' && isset($data['deliver_to_billing']) && $data['deliver_to_billing']) {
				$type = 'billing';
			}

			$address->lines[1]  = $data[$type]['address_line_1'];
			$address->lines[2]  = $data[$type]['address_line_2'];
			$address->lines[3]  = $data[$type]['address_line_3'];
			$address->lines[4]  = null;
			$address->town      = $data[$type]['town'];
			$address->postcode  = $data[$type]['postcode'];
			$address->telephone = $data[$type]['telephone'];

			if ($data[$type]['state_id']) {
				$address->state   = $this->get('state.list')->getByID($data[$type]['country_id'], $data[$type]['state_id']);
				$address->stateID = $data[$type]['state_id'];
			}

			$address->country   = $this->get('country.list')->getByID($data[$type]['country_id']);
			$address->countryID = $data[$type]['country_id'];
			$address->order     = $this->get('basket')->getOrder();
			$address->forename  = $data[$type]['forename'];
			$address->surname   = $data[$type]['surname'];

			$this->get('basket')->addAddress($address);
		}

		$this->addFlash('success', 'Addresses updated successfully');

		return $this->redirectToRoute('ms.ecom.checkout.confirm');
	}

	public function addressForm($type = 'billing', $action)
	{
		$address = $this->get('basket')->getOrder()->getAddress($type);
		// If it's false then set it to null
		$address = $address ?: null;

		$form = new UserDetails($this->_services);
		$form = $form->buildForm($this->get('user.current'), $address, $type, $action);

		return $form;
	}

}
