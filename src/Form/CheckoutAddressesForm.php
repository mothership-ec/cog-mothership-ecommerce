<?php

namespace Message\Mothership\Ecommerce\Form;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Mothership\Discount\Discount\Discount;
use Symfony\Component\Validator\Constraints;
use Message\Cog\ValueObject\DateTimeImmutable;

class CheckoutAddressesForm extends Form\AbstractType
{

	protected $_services;

	public function __construct($services)
	{
		$this->_services = $services;
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$types = $options['address_types'];

		$order = $this->_services['basket']->getOrder();

		foreach($types as $type) {
			$builder->add($type, $this->_services['address.form'], [
				'address_type'      => $type,
				'data'              => $order->getAddress($type) ?: null,
			]);
		}

		$deliverToDifferent = $order->getAddress('billing') != $order->getAddress('delivery');

		$builder->add('deliverToDifferent', 'checkbox', [
			'data'  => isset($options['data']) ? $options['data']->get('deliverToDifferent') : $deliverToDifferent,
			'label' => 'Deliver to different address',
		]);

		$builder->addEventListener(Form\FormEvents::SUBMIT, array($this, 'onSubmit'));
	}

	/**
	 * Method called on Form\FormEvents::SUBMIT
	 * @param  Form\FormEvent $event
	 */
	public function onSubmit(Form\FormEvent $event)
	{
		$data = $event->getData();

		if(false == $data['deliverToDifferent']) {
			$data['delivery'] = clone $data['billing'];
			$data['delivery']->type = 'delivery';
			$data['delivery']->id   = 'delivery';
			$event->setData($data);
		}
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(['address_types']);
		$resolver->setDefaults([
			'required' => false,
			'address_types' => [
				'delivery',
				'billing',
			],
			'validation_groups' => function(Form\FormInterface $form) {
				$data = $form->getData();
				if (false == $data['deliverToDifferent']) {
					return ['billing'];
				} else {
					return ['all'];
				}
			},
		]);
	}

	public function getName()
	{
		return 'checkout_addresses';
	}
}