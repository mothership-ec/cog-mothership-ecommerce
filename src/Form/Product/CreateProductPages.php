<?php

namespace Message\Mothership\Ecommerce\Form\Product;

use Message\Mothership\Ecommerce\ProductPage\Options;
use Message\Mothership\Ecommerce\ProductPage\UploadData\VariantNameCrawler;
use Message\Mothership\Ecommerce\ProductPage\UploadData\BrandValidator;

use Message\Cog\HTTP\Session;

use Message\Mothership\Commerce\Product\Upload\HeadingKeys;
use Message\Mothership\Commerce\Product\Upload\SessionNames;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;

use Message\Cog\Localisation\Translator;
use Message\Mothership\CMS\Page;

/**
 * Class CreateProductPages
 * @package Message\Mothership\Ecommerce\Form\Product
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class CreateProductPages extends Form\AbstractType
{
	const TRANS_PREFIX = 'ms.commerce.product.upload.csv.';
	const FIELD_NAME = 'create_product_pages';

	/**
	 * @var \Message\Cog\Localisation\Translator
	 */
	private $_trans;

	/**
	 * @var \Message\Mothership\CMS\Page\Loader
	 */
	private $_pageLoader;

	/**
	 * @var \Message\Cog\HTTP\Session
	 */
	private $_session;

	/**
	 * @var \Message\Mothership\Ecommerce\ProductPage\VariantNameCrawler
	 */
	private $_variantNameCrawler;

	/**
	 * @var \Message\Mothership\Ecommerce\ProductPage\BrandValidator
	 */
	private $_brandValidator;

	/**
	 * @var array
	 */
	private $_shopPages;

	public function __construct(
		Translator $trans,
		Page\Loader $pageLoader,
		Session $session,
		VariantNameCrawler $variantNameCrawler,
		BrandValidator $brandValidator,
		$shopPageIDs
	)
	{
		$this->_trans              = $trans;
		$this->_pageLoader         = $pageLoader;
		$this->_session            = $session;
		$this->_variantNameCrawler = $variantNameCrawler;
		$this->_brandValidator     = $brandValidator;
		$this->_setShopPages($shopPageIDs);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'ecom_create_product_pages';
	}

	/**
	 * @param Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
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

		$listingChoices = $this->_getListingChoices();

		if (count($listingChoices) > 1) {
			$builder->add(Options::LISTING_TYPE, 'choice', [
				'label'    => 'ms.ecom.product.upload.form.listing_type',
				'expanded' => true,
				'multiple' => false,
				'choices'  => $listingChoices,
				'data'     => key($listingChoices),
				'constraints' => [
					new Constraints\NotBlank,
				]
			]);
		} else {
			$builder->add(Options::LISTING_TYPE, 'hidden', [
				'data' => key($listingChoices),
				'constraints' => [
					new Constraints\NotBlank
				],
			]);
		}

		$builder->add(Options::PAGE_VARIANTS, 'choice', [
			'label'    => 'ms.ecom.product.upload.form.page_variants',
			'expanded' => true,
			'multiple' => false,
			'choices'  => $this->_getVariantOptions(),
			'data'     => key($this->_getVariantOptions()),
			'constraints' => [
				new Constraints\NotBlank
			]
		]);
	}

	/**
	 * @return array
	 */
	private function _getVariantOptions()
	{
		$options = [
			'individual' => 'ms.ecom.product.upload.form.individual',
		];

		$rows = $this->_session->get(SessionNames::VALID_ROWS_SESSION);

		$varNames = $this->_variantNameCrawler->getVariantNames($rows);

		foreach ($varNames as $key => $value) {
			if ($value === '') {
				unset($varNames[$key]);
			}
		}

		$options = $options + $varNames;

		return $options;
	}

	/**
	 * @return array
	 */
	private function _getListingChoices()
	{
		$rows = $this->_session->get(SessionNames::VALID_ROWS_SESSION);

		$choices = [
			'category' => 'ms.ecom.product.upload.form.category',
		];

		if ($this->_brandValidator->validBrands($rows)) {
			$choices['brand'] = 'ms.ecom.product.upload.form.brand';
		}

		ksort($choices);

		return $choices;
	}

	/**
	 * @param $shopPageIDs
	 * @throws \InvalidArgumentException
	 */
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