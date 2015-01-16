<?php

namespace Message\Mothership\Ecommerce\Form;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Mothership\Discount\Discount\Discount;
use Symfony\Component\Validator\Constraints;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Commerce\Order\Entity\Address\Address;

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
			'choices' => [
				'Mr'   => 'Mr',
				'Miss' => 'Miss',
				'Mrs'  => 'Mrs',
			],
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'data' => $this->_services['user.current']->title,
		]);

		$builder->add('forename','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'data' => $this->_services['user.current']->forename,
		]);
		$builder->add('surname','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
			'data' => $this->_services['user.current']->surname,
		]);
		$builder->add('address_line_1','text', [
			'property_path' => 'lines[1]',
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);
		$builder->add('address_line_2','text', [
			'property_path' => 'lines[2]',
		]);
		$builder->add('address_line_3','text', [
			'property_path' => 'lines[3]',
		]);
		$builder->add('address_line_4','text', [
			'property_path' => 'lines[4]',
		]);
		$builder->add('town','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);
		$builder->add('postcode','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);
		$builder->add('telephone', 'text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);
		$builder->add('stateID','choice', array(
			'label'   => 'State',
			'choices' => $this->_services['state.list']->all(),
			'empty_value' => 'Please select...',
			'attr'          => array(
				'data-state-filter-country-selector' => "#" . $type . "_countryID"
			),
		));

		$event = $this->_services['country.event'];

		$builder->add('countryID', 'choice', [
			'label'       => 'Country',
			'choices'     => $this->_services['event.dispatcher']->dispatch('country.'.$type, $event)->getCountries(),
			'empty_value' => 'Please select...',
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
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
			$form->get('stateID')->addError(new Form\FormError(sprintf('This value is required for %s addresses.',
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

		$resolver->setDefaults([
		   'data_class'   => 'Message\\Mothership\\Commerce\\Order\\Entity\\Address\\Address',
		]);
	}

	public function getName()
	{
		return 'address';
	}
}