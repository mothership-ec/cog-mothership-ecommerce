<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Localisation\Translator;
use Message\Mothership\Ecommerce\Gateway\Collection as GatewayCollection;
use Message\Mothership\Commerce\Order\Order;
use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class CheckoutConfirmForm extends Form\AbstractType
{
	private $_gateways;
	private $_translator;

	public function __construct(GatewayCollection $gateways, Translator $translator)
	{
		$this->_gateways = $gateways;
		$this->_translator = $translator;
	}

	public function getName()
	{
		return 'checkout_confirm';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults([
			'order' => null,
		]);
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		if (null !== $options['order'] && !$options['order'] instanceof Order) {
			throw new \InvalidArgumentException('`order` option must be an instance of Order or null');
		}

		$order = $options['order'];

		$builder->add('note', 'textarea', [
			'label' => 'ms.ecom.note.add',
			'data' => ($order && $order->notes->count()) ? $order->notes[0]->note : '',
		]);

		$this->_addSubmitButtons($builder);
	}

	private function _addSubmitButtons(Form\FormBuilderInterface $builder)
	{
		foreach ($this->_gateways as $gateway) {

			$label = 'ms.ecom.payment.' . $gateway->getName();

			if ($this->_translator->trans($label) === $label) {
				$label = $this->_translator->trans('ms.ecom.checkout.payment.gateway', [
					'%gateway%' => $gateway->getName(),
				]);
			}

			$builder->add($gateway->getName(), 'submit', [
				'label' => 'ms.ecom.payment.' . $gateway->getName(),
				'attr' => [
					'class' => $gateway->getName(),
				],
			]);
		}
	}
}