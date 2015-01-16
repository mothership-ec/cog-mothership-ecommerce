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
		$form = $this->createForm($this->get('checkout.form.register'));

		$form->handleRequest();

		if ($form->isValid()) {
			$data = $form->getData();

			// Build and create the user
			$user = $this->get('user');
			$user->forename = $data['addresses']['billing']->forename;
			$user->surname  = $data['addresses']['billing']->surname;
			$user->password = $data['password'];
			$user->email    = $data['email'];
			$user->title    = $data['addresses']['billing']->title;

			try {
				$user = $this->get('user.create')->save($user);
			} catch (\Exception $e) {
				$this->addFlash('error', 'Email address is already in use');

				return $this->render('Message:Mothership:Ecommerce::checkout:stage-1c-register', array(
					'form' => $form,
				));
			}

			// Set the user session
			$this->get('http.session')->set($this->get('cfg')->user->sessionName, $user);

			// Fire the user login event
			$this->get('event.dispatcher')->dispatch(
				\Message\User\Event\Event::LOGIN,
				new \Message\User\Event\Event($user)
			);

			$addresses = [];
			foreach (['delivery','billing'] as $type) {
				$address = $data['addresses'][$type];
				$address->order = $this->get('basket')->getOrder();

				$addresses[] = $address;
			}

			$this->get('basket')->setEntities('addresses', $addresses);

			return $this->redirectToRoute('ms.ecom.checkout.confirm');
		}

		return $this->render('Message:Mothership:Ecommerce::checkout:stage-1c-register', array(
			'form' => $form,
		));
	}

	/**
	 * Output for the editing of addresses
	 */
	public function addresses()
	{
		$form = $this->createForm($this->get('checkout.form.addresses'));

		$form->handleRequest();

		if($form->isValid()) {
			$data = $form->getData();

			$addresses = [];

			foreach (['delivery','billing'] as $type) {
				$address = $data[$type];
				if ($data['save']) {
					$currentAddress = $this->get('user.address.loader')->getByUserAndType($this->get('user.current'), $type);
					if ($currentAddress) {
						$address->id = $currentAddress->id;
						$this->get('user.address.edit')->save($address);
					} else {
						$this->get('user.address.create')->create($address);
					}
				}
				$address->order = $this->get('basket')->getOrder();

				$addresses[] = $address;
			}

			$this->get('basket')->setEntities('addresses', $addresses);

			$this->addFlash('success', 'Addresses updated successfully');

			return $this->redirectToRoute('ms.ecom.checkout.confirm');
		}

		return $this->render('Message:Mothership:Ecommerce::checkout:stage-1b-change-addresses', array(
			'form' => $form,
		));
	}
}
