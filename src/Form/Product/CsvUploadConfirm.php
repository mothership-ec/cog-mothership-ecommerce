<?php

namespace Message\Mothership\Ecommerce\Form\Product;

use Message\Mothership\Ecommerce\ProductPage\Options;
use Message\Mothership\Ecommerce\ProductPage\VariantNameCrawler;

use Message\Cog\Routing\UrlGenerator;
use Message\Cog\HTTP\Session;

use Message\Mothership\Commerce\Product\Upload\HeadingKeys;
use Message\Mothership\Commerce\Product\Upload\SessionNames;
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
	private $_session;
	private $_variantNameCrawler;

	private $_shopPages;

	public function __construct(
		UrlGenerator $urlGenerator,
		Translator $trans,
		Page\Loader $pageLoader,
		Session $session,
		VariantNameCrawler $variantNameCrawler,
		$shopPageIDs
	)
	{
		parent::__construct($urlGenerator);
		$this->_trans              = $trans;
		$this->_pageLoader         = $pageLoader;
		$this->_session            = $session;
		$this->_variantNameCrawler = $variantNameCrawler;
		$this->_setShopPages($shopPageIDs);
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add(Options::CREATE_PAGES, 'checkbox', [
			'label' => 'ms.ecom.product.upload.form.create',
		]);

		if (count($this->_shopPages) > 1) {
			$builder->add(Options::PARENT, 'choice', [
				'label' => 'ms.ecom.product.upload.form.parent',
				'expanded' => true,
				'multiple' => false,
				'choices' => $this->_shopPages,
				'data'    => key($this->_shopPages),
				'constraints' => [
					new Constraints\NotBlank,
				]
			]);
		} else {
			$builder->add(Options::PARENT, 'hidden', [
				'data' => key($this->_shopPages),
				'constraints' => [
					new Constraints\NotBlank,
				]
			]);
		}

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

		$rows = $this->_session->get(SessionNames::VALID_ROWS_SESSION);
		$options = $options + $this->_variantNameCrawler->getVariantNames($rows);

		return $options;
	}

	private function _getListingChoices()
	{
		return [
			'brand'    => 'ms.ecom.product.upload.form.brand',
			'category' => 'ms.ecom.product.upload.form.category',
		];
	}

	private function _setShopPages($shopPageIDs)
	{
		if (!is_array($shopPageIDs)) {
			$shopPageIDs = [$shopPageIDs];
		}

		foreach ($shopPageIDs as $id) {
			if (!is_int($id)) {
				throw new \InvalidArgumentException('Shop page IDs must all be integers');
			}
		}

		$pages = (array) $this->_pageLoader->getByID($shopPageIDs);
		$shopPages = [];

		foreach ($pages as $page) {
			$shopPages[$page->id] = $page->title;
		}

		reset($shopPages);

		$this->_shopPages = $shopPages;
	}
}