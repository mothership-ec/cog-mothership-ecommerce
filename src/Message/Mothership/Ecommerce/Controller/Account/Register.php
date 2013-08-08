<?php

namespace Message\Mothership\Ecommerce\Controller\Account;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\Form\UserRegister;

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
			de($data);
		} else {
			de($form->getMessages());
		}
	}

}