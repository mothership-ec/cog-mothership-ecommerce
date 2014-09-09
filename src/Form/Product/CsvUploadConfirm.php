<?php

namespace Message\Mothership\Ecommerce\Form\Product;

use Message\Mothership\Ecommerce\ProductPage\Options;

use Message\Mothership\Commerce\Product\Upload\HeadingKeys;
use Message\Mothership\Commerce\Form\Product\CsvUploadConfirm as BaseForm;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;

use Message\Cog\Localisation\Translator;
use Message\Mothership\CMS\Page;

class CsvUploadConfirm extends BaseForm
{
	const TRANS_PREFIX = 'ms.commerce.product.upload.csv.';

	private $_trans;
	private $_pageLoader;

	public function __construct(Translator $trans, Page\Loader $pageLoader)
	{
		$this->_trans      = $trans;
		$this->_pageLoader = $pageLoader;
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add(Options::CREATE_PAGES, 'checkbox', [
			'label' => 'ms.ecom.product.upload.form.create',
		]);

		$builder->add(Options::PARENT, 'choice', [
			'label'   => 'ms.ecom.product.upload.form.parent',
			'choices' => $this->_getPageChoices(),
		]);

		$builder->add(Options::LISTING_TYPE, 'choice', [
			'label'    => 'ms.ecom.product.upload.form.listing_type',
			'expanded' => true,
			'multiple' => false,
			'choices'  => $this->_getListingChoices(),
		]);

		$builder->add(Options::PAGE_VARIANTS, 'choice', [
			'label'    => 'ms.ecom.product.upload.form.page_variants',
			'expanded' => true,
			'multiple' => false,
			'choices'  => $this->_getVariantOptions(),
			'constraints' => [
				new Constraints\NotBlank
			]
		]);
	}

	private function _getVariantOptions()
	{
		$options = [
			'individual' => 'ms.ecom.product.upload.form.individual',
		];

		for ($i = 1; $i <= HeadingKeys::NUM_VARIANTS; $i++) {
			$options[HeadingKeys::VAR_NAME_PREFIX . $i] =
				$this->_trans->trans(self::TRANS_PREFIX . HeadingKeys::VAR_NAME_PREFIX) . ' ' . $i;
		}

		return $options;
	}

	private function _getListingChoices()
	{
		return [
			'brand'    => 'ms.ecom.product.upload.form.brand',
			'category' => 'ms.ecom.product.upload.form.category',
		];
	}

	private function _getPageChoices()
	{
		$pages = $this->_pageLoader->getTopLevel();

		array_walk($pages, function(&$page) {
			if (!$page instanceof Page\Page) {
				throw new \LogicException('Expecting Page object, ' . gettype($page) . ' given');
			}

			$page = $page->title;
		});

		return $pages;
	}
}