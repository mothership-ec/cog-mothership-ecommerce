<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Message\User\UserInterface;
use Message\Mothership\Commerce\Order\Entity\Address\Address;

class UserRegister extends Handler
{

	public function __construct(Container $container)
	{
		parent::__construct($container);
	}

	public function buildForm($action)
	{

		$this->setName('register')
			->setMethod('POST')
			->setAction($action);

		$this->add('title','choice','', array(
			'choices' => array(
				'mr'   => 'Mr',
				'miss' => 'Miss',
				'mrs'  => 'Mrs',
			)
		));

		$this->add('forename','text','Forename');
		$this->add('surname','text','');
		$this->add('email','text','');
		$this->add('password','password','');
		$this->add('password_confirm','password','');

		return $this;
	}

}