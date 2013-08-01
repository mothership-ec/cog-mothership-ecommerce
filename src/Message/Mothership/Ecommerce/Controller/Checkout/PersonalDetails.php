<?php

namespace Message\Mothership\Ecommerce\Controller\Checkout;

use Message\Cog\Controller\Controller;

/**
 * Class Checkout
 * @package Message\Mothership\Ecommerce\Controller\Fulfillment
 *
 * Controller for processing orders in Fulfillment
 */
class PersonalDetails extends Controller
{
	public function index()
	{
		$basket = $this->get('basket');

		if (1) {

		}

		return $this->render('Message:Mothership:Ecommerce::Checkout:personal-details', array(
			'order'    => $this->get('basket')->getOrder(),
		));
	}

	public function addresses()
	{
		return $this->render('Message:Mothership:Ecommerce::Checkout:personal-details-addresses', array(
			'form'    => $this->addressForm(),
		));
	}

	public function addressProcess()
	{
		$form = $this->addressForm();
		if ($form->isValid() && $data = $form->getFilteredData()) {
			foreach($data['address'] as $type => $values) {

				$address = new \Message\Mothership\Commerce\Order\Entity\Address\Address;

				$address->type     = $type;
				$address->lines[1] = $values['address_line_1'];
				$address->lines[2] = $values['address_line_2'];
				$address->lines[3] = $values['address_line_3'];
				$address->lines[4] = $values['address_line_4'];
				$address->town     = $values['town'];
				$address->postcode = $values['postcode'];
				$address->country  = $this->get('country.list')->getByID($values['country_id']);
				$address->countryID = $values['country_id'];
				$address->order    = $this->get('basket')->getOrder();
				$address->name 	   = ucfirst($values['title']).' '.$values['forename'].' '.$values['surname'];

				$this->get('basket')->updateAddress($address);
			}

			$this->addFlash('success', 'Addresses updated successfully');

		}

		return $this->redirectToReferer();

	}

	public function addressForm()
	{
		$form = $this->get('form');

		$addressForms = $this->get('form')
			->setName('address')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.ecom.checkout.personal.details.addresses.action'))
			->addOptions(array(
				'auto_initialize' => false,
			)
		);

		$types = array(
			'billing',
			'delivery',
		);

		foreach ($types as $type) {
			$address = array_pop($this->get('basket')
				->getOrder()
				->addresses
				->getByProperty('type',$type));

			$defaults = array(
				'forename'       => $address->name,
				'surname'        => $address->name,
				'address_line_1' => $address->lines[1],
				'address_line_2' => $address->lines[2],
				'address_line_3' => $address->lines[3],
				'address_line_4' => $address->lines[4],
				'town'           => $address->town,
				'postcode'       => $address->postcode,
				'state'          => $address->state,
				'country_id'      => $address->countryID,
			);
			$types = $this->get('form')
				->setName($type)
				->setDefaultValues($defaults)
				->addOptions(array(
					'auto_initialize' => false,
				)
			);

			$types->add('title','choice','', array(
				'choices' => array(
					'mr' => 'Mr',
					'miss' => 'Miss',
					'mrs' => 'Mrs',
				)
			));
			$types->add('forename','text','Forename');
			$types->add('surname','text','');
			$types->add('address_line_1','text','');
			$types->add('address_line_2','text','');
			$types->add('address_line_3','text','');
			$types->add('address_line_4','text','');
			$types->add('town','text','');
			$types->add('postcode','text','');
			$types->add('state','text','');
			$types->add('country_id','choice','', array(
				'choices' => $this->get('country.list')->all()
			));
			$addressForms->add($types->getForm(),'form');
		}

		$form->add($addressForms->getForm(),'form');

		return $form;
	}

}
