<?php

namespace Message\Mothership\Ecommerce\Controller\Account;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\Form\UserRegister;
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
		$form = new UserRegister($this->_services);

		return $form->buildForm($this->generateUrl('ms.ecom.register.action'));
	}

	public function registerProcess()
	{
		$form = $this->registerForm();
		if ($form->isValid() && $data = $form->getFilteredData()) {

			if ($data['password'] !== $data['password_confirm']) {
				$this->addFlash('error', 'Your passwords do not match');
				return $this->redirectToReferer();
			}

			$user = $this->get('user');
			$user->forename = $data['forename'];
			$user->surname = $data['surname'];
			$user->password = $data['password'];
			$user->email = $data['email'];
			$user->title = $data['title'];

			$user = $this->get('user.create')->save($user);

			// Set the user session
			$this->get('http.session')->set($this->get('cfg')->user->sessionName, $user);

			// Fire the user login event
			$this->get('event.dispatcher')->dispatch(
				Event\Event::LOGIN,
				new Event\Event($user)
			);

			$this->addFlash('success','User created successfully');
		}

		return $this->redirectToRoute('ms.ecom.checkout.details');
	}

}