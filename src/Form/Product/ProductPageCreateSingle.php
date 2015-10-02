<?php

namespace Message\Mothership\Ecommerce\Form\Product;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Mothership\CMS\Page\Loader as PageLoader;
use Message\Mothership\Commerce\Product\OptionLoader;
use Message\Cog\Form\Extension\Type\LinkedChoice;

/**
 * @author Samuel Trangmar-Keates <sam@message.co.uk>
 *
 * The form to allow quick creation of product pages.
 */
class ProductPageCreateSingle extends Form\AbstractType
{
	protected $_pageLoader;
	protected $_optionLoader;

	public function __construct(PageLoader $pageLoader, OptionLoader $optionLoader)
	{
		$this->_pageLoader = $pageLoader;
		$this->_optionLoader = $optionLoader;
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$product = $options['product'];

		$builder->add('product', 'hidden', ['data' => $product->id]);

		$parents = $this->_pageLoader->getAll();
		$choices = [];
		if ($parents) {
			foreach ($parents as $p) {
				$spaces = str_repeat('--', $p->depth + 1);
				// don't display the option to move it to a page which doesn't allow children
				if (!$p->type->allowChildren()) {
					continue;
				}

				$choices[$p->id] = $spaces.' '.$p->title;
			}
		}

		$builder->add('parent', 'choice', [
			'choices' => $choices,
			'required' => true,
		]);

		$names  = $this->_optionLoader->getAllOptionNames();
		$values = $this->_optionLoader->getAllOptionValues();

		$unitOptions = new LinkedChoice([
			'option_name'  => array_combine($names, $names),
			'option_value' => array_combine($values, $values),
		]);

		$unitOptions->buildForm($builder, $options);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(['product']);
		$resolver->setAllowedTypes([
			'product' => '\\Message\\Mothership\\Commerce\\Product\\Product'
		]);
	}

	public function getName()
	{
		return 'ecom_product_page_create';
	}
}