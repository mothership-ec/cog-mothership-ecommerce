<?php

namespace Message\Mothership\Ecommerce\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;
use Message\Cog\Localisation\Translator;

class ProductSelector extends AbstractType
{
	private $_translator;

	public function __construct(Translator $trans)
	{
		$this->_translator = $trans;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$units = $options['units'];

		$selectorData = [];
		$maxStock     = isset($options['max_stock']) ? $options['max_stock'] : 0;

		foreach ($units as $unit) {
			$stock = $unit->getStockForLocation($options['location']);
			if (!isset($options['max_stock'])) {
				$maxStock = max($stock, $maxStock);
			}

			if ($stock || $options['show_out_of_stock']) {
				$opts = $unit->options;
				if ($options['variant_key']) {
					unset($opts[$options['variant_key']]);
				}
				
				$display = implode(', ', $opts);

				$selectorData[$unit->id] = $display;
			}
		}

		$builder->add('unit_id', 'unit_choice', [
			'choices' => $selectorData,
			'oos'     => $options['out_of_stock_units'],
			'empty_value'  => $options['unit_placeholder'],
			'constraints' => [
				new Constraints\NotBlank,
			]
		]);

		$builder->add('quantity', 'choice', [
			'choices' => range(1, $maxStock),
			'constraints' => [
				new Constraints\NotBlank,
			]
		]);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired([
			'units',
			'location',
		]);

		$resolver->setOptional([
			'max_stock',
		]);

		$resolver->setDefaults([
			'variant_key'        => null ,
			'show_out_of_stock'  => false,
			'out_of_stock_units' => [],
			'unit_placeholder'   => $this->_translator->trans('ms.ecom.shop.product.select.placeholder'),
		]);
	}

	public function getName()
	{
		return 'select_product';
	}
}