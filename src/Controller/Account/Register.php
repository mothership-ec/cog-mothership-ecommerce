<?php

namespace Message\Mothership\Ecommerce\Controller\Account;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\Form\UserRegister;
use Message\Mothership\Ecommerce\Form\CheckoutRegisterForm;
use Message\User\Event;

/**
 * Class Register
 */
class Register extends Controller
{
	public function register()
	{
		return $this->render('Message:Mothership:Ecommerce::Account:register', array(
			'form'   => $this->registerForm(),
		));
	}

	public function registerForm()
	{
		$form = new CheckoutRegisterForm($this->_services);

		return $this->createForm($form);
	}

	public function registerProcess()
	{
		$form = $this->registerForm();
		$form->handleRequest();

		if ($form->isValid()) {

			$data = $form->getData();

			// @todo this should probably happen in a data transformer
			$billingAddress  = $data['addresses']['billing'];
			$deliveryAddress = $data['addresses']['delivery'] ?: $billingAddress;

			$user = $this->get('user');
			$user->forename = $deliveryAddress->forename;
			$user->surname  = $deliveryAddress->surname;
			$user->title    = $deliveryAddress->title;
			$user->password = $data['password'];
			$user->email    = $data['email'];

			$user = $this->get('user.create')->create($user);

			$this->get('basket')->setEntities('addresses', [
				$billingAddress,
				$deliveryAddress
			]);

			if (!$this->get('basket')->getOrder()->getAddress('delivery')) {
				throw new \LogicException('Delivery address not added to session BEFORE user is set');
			}

			// Set the user session
			$this->get('http.session')->set($this->get('cfg')->user->sessionName, $user);

			if (!$this->get('basket')->getOrder()->getAddress('delivery')) {
				throw new \LogicException('Delivery address not added to session AFTER user is set but BEFORE event is fired');
			}

			// Fire the user login event
			$this->get('event.dispatcher')->dispatch(
				Event\Event::LOGIN,
				new Event\Event($user)
			);

			if (!$this->get('basket')->getOrder()->getAddress('delivery')) {
				throw new \LogicException('Delivery address not added to session AFTER event is fired');
			}

			$this->addFlash('success','User created successfully');
		}

		return $this->redirectToRoute('ms.ecom.checkout.details.addresses');
	}

}