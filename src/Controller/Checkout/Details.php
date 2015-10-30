<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Cog\Controller\Controller;
use Message\Mothership\User\Address\Address;
use Message\Mothership\Ecommerce\Checkout;

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

			if ($this->get('user.loader')->getByEmail($data['email'])) {
				$this->addFlash('error', $this->trans('ms.ecom.user.register.email-in-use', [
					'%forgottenLink%' => $this->generateUrl('ms.user.password.request'),
				]));

				return $this->render('Message:Mothership:Ecommerce::checkout:stage-1c-register', array(
					'form' => $form,
				));
			}

			// Build and create the user
			$user = $this->get('user');
			$user->forename = $data['addresses']['billing']->forename;
			$user->surname  = $data['addresses']['billing']->surname;
			$user->password = $data['password'];
			$user->email    = $data['email'];
			$user->title    = $data['addresses']['billing']->title;

			$user = $this->get('user.create')->save($user);

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

			return $this->redirectToRoute('ms.ecom.checkout.details.addresses');
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

			$displaySaveFlash = false;
			foreach (['delivery','billing'] as $type) {
				$address = $data[$type];

				// Save addresses if selected
				if (!empty($data['save'])) {
					$currentAddress = $this->get('user.address.loader')->getByUserAndType($this->get('user.current'), $type);
					if ($currentAddress) {
						$address->id = $currentAddress->id;
						$this->get('user.address.edit')->save($address);
					} else {
						$currentAddress = new Address;
						$this->get('user.address.create')->create($address);
					}

					if (false === $displaySaveFlash && $currentAddress->flatten() !== $address->flatten()) {
						$this->addFlash('success', $this->trans('ms.ecom.checkout.address.save_success'));
						$displaySaveFlash = true;
					}
				}

				$address->order = $this->get('basket')->getOrder();

				$addresses[] = $address;
			}

			$this->get('basket')->setEntities('addresses', $addresses);

			$this->get('event.dispatcher')->dispatch(
				Checkout\Events::ADDRESSES,
				new Checkout\Event($this->get('basket')->getOrder(), $data)
			);

			return $this->redirectToRoute('ms.ecom.checkout.confirm');
		}

		return $this->render('Message:Mothership:Ecommerce::checkout:stage-1b-change-addresses', array(
			'form' => $form,
		));
	}
}
