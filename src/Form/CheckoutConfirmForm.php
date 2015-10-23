<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Localisation\Translator;
use Message\Mothership\Ecommerce\Gateway\Collection as GatewayCollection;
use Message\Mothership\Commerce\Order\Order;
use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Class CheckoutConfirmForm
 * @package Message\Mothership\Ecommerce\Form
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for checkout confirm screen. Replaces the deprecated form provided by Controller\Confirm::continueForm()
 * and allows for multiple payment gateways. For each gateway a different submit button is created, the controller
 * then checks which one has been clicked and uses the matching payment gateway.
 */
class CheckoutConfirmForm extends Form\AbstractType
{
	/**
	 * @var GatewayCollection
	 */
	private $_gateways;

	/**
	 * @var Translator
	 */
	private $_translator;

	/**
	 * @param GatewayCollection $gateways   Gateways are given to generated submit buttons
	 * @param Translator $translator        Translator is used to determine accurate text for submit buttons. If no
	 *                                      translation exists for that gateway, it will use a default translation
	 */
	public function __construct(GatewayCollection $gateways, Translator $translator)
	{
		$this->_gateways = $gateways;
		$this->_translator = $translator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'checkout_confirm';
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults([
			'order' => null,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		if (null !== $options['order'] && !$options['order'] instanceof Order) {
			throw new \InvalidArgumentException('`order` option must be an instance of Order or null');
		}

		$order = $options['order'];

		$builder->add('note', 'textarea', [
			'label' => 'ms.ecom.checkout.note.add',
			'data' => ($order && $order->notes->count()) ? $order->notes[0]->note : '',
		]);

		$this->_addSubmitButtons($builder);
	}

	/**
	 * Loop through gateways and create submit buttons to give to the form. The translator will attempt
	 * to find a string determined by the gateway for that
	 *
	 * @param Form\FormBuilderInterface $builder
	 */
	private function _addSubmitButtons(Form\FormBuilderInterface $builder)
	{
		foreach ($this->_gateways as $gateway) {

			$transString = 'ms.' . $gateway->getName() . '.payment.button';

			$label = $this->_translator->trans($transString);

			if ($label === $transString) {
				$label = $this->_translator->trans('ms.ecom.checkout.payment.gateway', [
					'%gateway%' => $this->_convertName($gateway->getName()),
				]);
			}

			$builder->add($gateway->getName(), 'submit', [
				'label' => $label,
			]);
		}
	}

	/**
	 * Convert gateway name to a 'human readable' name by replacing hyphens with spaces and capitalising each word.
	 *
	 * @param $name
	 *
	 * @return string
	 */
	private function _convertName($name)
	{
		$name = explode('-', $name);

		array_walk($name, function (&$word) {
			$word = ucfirst($word);
		});

		return implode(' ', $name);
	}
}