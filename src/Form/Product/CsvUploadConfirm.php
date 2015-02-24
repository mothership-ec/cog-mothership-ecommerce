<?php

namespace Message\Mothership\Ecommerce\Form\Product;

use Message\Mothership\Ecommerce\ProductPage\Options;

use Message\Mothership\Commerce\Form\Product\CsvUploadConfirm as BaseForm;
use Message\Cog\Routing\UrlGenerator;

use Symfony\Component\Form;

class CsvUploadConfirm extends BaseForm
{
	/**
	 * @var CreateProductPages
	 */
	private $_subForm;

	private $_hasShopPages;

	public function __construct(UrlGenerator $urlGenerator, CreateProductPages $subForm, $hasShopPages)
	{
		parent::__construct($urlGenerator);

		$this->_subForm      = $subForm;
		$this->_hasShopPages = (bool) $hasShopPages;
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);

		if ($this->_hasShopPages) {
			$builder->add(Options::CREATE_PAGES, 'checkbox', [
				'label' => 'ms.ecom.product.upload.form.create',
			]);

			$builder->add(CreateProductPages::FIELD_NAME, $this->_subForm);
		} else {
			$builder->add(Options::CREATE_PAGES, 'hidden', [
				'data' => false,
			]);
		}
	}
}