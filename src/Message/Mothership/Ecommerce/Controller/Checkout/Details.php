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
		return $this->render('Message:Mothership:Ecommerce::Checkout:stage-1a-login-register', array(
			'order'    => $this->get('basket')->getOrder(),
		));
	}

	public function register()
	{
		return $this->render('Message:Mothership:Ecommerce::Checkout:stage-1c-register', array(
			'form' => $this->registerForm(),
		));
	}

	public function registerForm()
	{
		$form = $this->get('form');
		$form->setMethod('POST')
			->setName('register')
			->setAction($this->generateUrl('ms.ecom.checkout.details.register.process'));

		$types = array(
			'billing',
			'delivery',
		);

		foreach ($types as $type) {
			$typeForm = $this->get('form');

			$typeForm
				->setName($type)
				->addOptions(array(
				'auto_initialize' => false,
			));

			$typeForm->add('title','choice','', array(
				'choices' => array(
					'mr'   => 'Mr',
					'miss' => 'Miss',
					'mrs'  => 'Mrs',
			)));

			$typeForm->add('forename','text','Forename');
			$typeForm->add('surname','text','');
			$typeForm->add('address_line_1','text','');
			$typeForm->add('address_line_2','text','')
				->val()->optional();
			$typeForm->add('address_line_3','text','')
				->val()->optional();
			$typeForm->add('town','text','');
			$typeForm->add('postcode','text','');
			$typeForm->add('state_id','text','State')
				->val()->optional();

			$typeForm->add('country_id','choice','Country', array(
				'choices' => $this->get('country.list')->all()
			));

			$form->add($typeForm->getForm(), 'form');
		}

		$form->add('deliver_to_billing','checkbox', '')
			->val()
			->optional();

		$form->add('email','email','Email Address');
		$form->add('password','password','Password');
		$form->add('password_check','password','Password again');

		return $form;
	}

	public function registerProcess()
	{
		$form = $this->registerForm();
		$data = $form->getData();


		// If we are delivering to the billing address we only need to validate
		// that address, which we will have to do manually
		if (isset($data['deliver_to_billing']) && $data['deliver_to_billing']) {
			$check = array(
				'title',
				'forename',
				'surname',
				'address_line_1',
				'town',
				'postcode',
				'country_id',
			);

			foreach ($data['billing'] as $key => $value) {
				if (isset($check[$key]) && (empty($value) || !$value)) {
					$this->addFlash('error', 'Please ensure all required fields have been completed');

					return $this->redirectToReferer();
				}
			}
		} else {
			// Validate the full form input
			if (!$form->isValid()) {
				$this->addFlash('error', 'Please ensure all required fields have been completed');

				return $this->redirectToReferer();
			}
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

		$user = $this->get('user');
		$user->forename = $data['billing']['forename'];
		$user->surname = $data['billing']['surname'];
		$user->password = $data['password'];
		$user->email = $data['email'];
		$user->title = $data['billing']['title'];

		if (!$user = $this->get('user.create')->save($user)) {
			$this->addFlash('error', 'User could not be created');

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

		return $this->redirectToRoute('ms.ecom.checkout.delivery');
	}

	public function addresses()
	{
		$billing  = $this->addressForm('billing', $this->generateUrl('ms.ecom.checkout.details.addresses.action', array('type' => 'billing')));
		$delivery = $this->addressForm('delivery', $this->generateUrl('ms.ecom.checkout.details.addresses.action', array('type' => 'delivery')));

		return $this->render('Message:Mothership:Ecommerce::Checkout:stage-1b-change-addresses', array(
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
		$address = $this->get('basket')->getOrder()->getAddress($type);
		// If it's false then set it to null
		$address = $address ?: null;

		$form = new UserDetails($this->_services);
		$form = $form->buildForm($this->get('user.current'), $address, $type, $action);

		return $form;
	}

}
