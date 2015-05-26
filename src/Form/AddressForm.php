<?php

namespace Message\Mothership\Ecommerce\Form;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class AddressForm extends Form\AbstractType
{

	protected $_services;

	public function __construct($services)
	{
		$this->_services = $services;
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$type = $options['address_type'];

		$builder->add('title','choice', [
			'choices' => $this->_services['title.list'],
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);

		$builder->add('forename','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'label' => $this->_services['translator']->trans('ms.ecom.user.firstname'),
		]);
		$builder->add('surname','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'label' => $this->_services['translator']->trans('ms.ecom.user.lastname'),
		]);
		$builder->add('address_line_1','text', [
			'property_path' => 'lines[1]',
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'label' => $this->_services['translator']->trans('ms.ecom.user.address.line1'),
		]);
		$builder->add('address_line_2','text', [
			'property_path' => 'lines[2]',
			'label' => $this->_services['translator']->trans('ms.ecom.user.address.line2'),
		]);
		$builder->add('address_line_3','text', [
			'property_path' => 'lines[3]',
			'label' => $this->_services['translator']->trans('ms.ecom.user.address.line3'),
		]);
		$builder->add('address_line_4','text', [
			'property_path' => 'lines[4]',
			'label' => $this->_services['translator']->trans('ms.ecom.user.address.line4'),
		]);
		$builder->add('town','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'label' => $this->_services['translator']->trans('ms.ecom.user.address.town'),
		]);
		$builder->add('postcode','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'label' => $this->_services['translator']->trans('ms.ecom.user.address.postcode'),
		]);
		$builder->add('telephone', 'text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'label' => $this->_services['translator']->trans('ms.ecom.user.address.telephone'),
		]);
		$builder->add('stateID','choice', array(
			'choices' => $this->_services['state.list']->all(),
			'empty_value' => $this->_services['translator']->trans('ms.ecom.please-select'),
			'attr'          => array(
				'data-state-filter-country-id' => $type . "_countryID"
			),
			'label' => $this->_services['translator']->trans('ms.ecom.user.address.state'),
		));

		$event = $this->_services['country.event'];

		$builder->add('countryID', 'choice', [
			'choices'     => $this->_services['event.dispatcher']->dispatch('country.'.$type, $event)->getCountries(),
			'empty_value' => $this->_services['translator']->trans('ms.ecom.please-select'),
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'label' => $this->_services['translator']->trans('ms.ecom.user.address.country'),
		]);

		$builder->addEventListener(Form\FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
	}

	public function onPostSubmit(Form\FormEvent $event)
	{
		$form = $event->getForm();
		$this->validateState($form);
		$this->filter($form);
	}

	public function validateState(Form\FormInterface $form)
	{
		$states = $this->_services['state.list']->all();
		$address = $form->getData();

		if (isset($states[$address->countryID]) and (empty($address->stateID) or ! isset($states[$address->countryID][$address->stateID]))) {
			$form->get('stateID')->addError(new Form\FormError(sprintf($this->_services['translator']->trans('ms.ecom.user.address.state-required'),
				$this->_services['country.list']->getByID($address->countryID)
			)));
		}
	}

	public function filter(Form\FormInterface $form)
	{
		$type = $form->getConfig()->getOption('address_type');
		$address = $form->getData();

		$address->type    = $type;
		$address->id      = $type;
		$address->state   = $this->_services['state.list']->getByID($address->countryID, $address->stateID);
		$address->country = $this->_services['country.list']->getByID($address->countryID);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(['address_type']);

		$resolver->setOptional(['address']);

		$resolver->setDefaults([
		   'data_class'   => 'Message\\Mothership\\Commerce\\Order\\Entity\\Address\\Address',
		]);
	}

	public function getName()
	{
		return 'address';
	}
}