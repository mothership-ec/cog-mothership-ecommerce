<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserDetails extends Handler
{

	public function __construct()
	{

	}

	public function buildForm(User $user, Address $address, $action)
	{
		$form = $this->_form;
		$form->add('address','text', array());

		return $form;
	}

}