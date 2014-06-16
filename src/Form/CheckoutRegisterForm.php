<?php

namespace Message\Mothership\Ecommerce\Form;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Mothership\Discount\Discount\Discount;
use Symfony\Component\Validator\Constraints;
use Message\Cog\ValueObject\DateTimeImmutable;

class CheckoutRegisterForm extends Form\AbstractType
{

	protected $_services;

	public function __construct($services)
	{
		$this->_services = $services;
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add('addresses', $this->_services['checkout.form.addresses'], [
			'label' => false,
		]);

		$builder->add('email','email', [
			'label' => 'E-mail Address',
			'constraints' => new Constraints\NotBlank,
		]);
		$builder->add('password','repeated', [
			'type' => 'password',
			'first_options' => [
				'label' => 'Password',
			],
			'second_options' => [
				'label' => 'Repeat Password',
			],
			'constraints' => new Constraints\NotBlank,
		]);
	}

	public function getName()
	{
		return 'checkout_register';
	}
}