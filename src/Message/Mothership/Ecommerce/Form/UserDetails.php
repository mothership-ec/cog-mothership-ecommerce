<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Message\User\UserInterface;
use Message\Mothership\Commerce\Address\Address;

class UserDetails extends Handler
{

	public function __construct(Container $container)
	{
		parent::__construct($container);
	}

	public function buildForm(UserInterface $user, Address $address = null, $type, $action = '')
	{
		$defaults = array();
		if (!is_null($address)) {
			$defaults = array(
				'title'			 => strtolower($user->title),
				'forename'       => $address->forename,
				'surname'        => $address->surname,
				'address_line_1' => $address->lines[1],
				'address_line_2' => $address->lines[2],
				'address_line_3' => $address->lines[3],
				'address_line_4' => $address->lines[4],
				'town'           => $address->town,
				'postcode'       => $address->postcode,
				'state_id'       => $address->stateID,
				'country_id'     => $address->countryID,
			);
		}

		$this->setName($type)
			->setMethod('POST')
			->setDefaultValues($defaults)
			->setAction($action)
			->addOptions(array(
				'auto_initialize' => false,
			));

		$this->add('title','choice','', array(
			'choices' => array(
				'mr'   => 'Mr',
				'miss' => 'Miss',
				'mrs'  => 'Mrs',
			)
		));

		$this->add('forename','text','Forename');
		$this->add('surname','text','');
		$this->add('address_line_1','text','');
		$this->add('address_line_2','text','')
			->val()->optional();
		$this->add('address_line_3','text','')
			->val()->optional();
		$this->add('address_line_4','text','')
			->val()->optional();
		$this->add('town','text','');
		$this->add('postcode','text','');
		$this->add('state_id','text','')
			->val()->optional();

		$this->add('country_id','choice','', array(
			'choices' => $this->_container['country.list']->all()
		));


		return $this;
	}

}