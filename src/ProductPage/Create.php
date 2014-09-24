<?php

namespace Message\Mothership\Ecommerce\ProductPage;

use Message\Mothership\Commerce\Product;
use Message\Mothership\Commerce\Product\Upload\HeadingKeys;

use Message\Mothership\CMS\Page;
use Message\Mothership\CMS\PageType;

use Message\Cog\ValueObject\DateRange;

/**
 * @todo move parent creation stuff to ParentCreate class
 *
 * Class Create
 * @package Message\Mothership\Ecommerce\ProductPage
 */
class Create
{
	const PAGE_TYPE  = 'product';
	const INDIVIDUAL = 'individual';

	private $_pageCreate;
	private $_pageTypes;
	private $_contentLoader;
	private $_contentEdit;
	private $_headingKeys;
	private $_listingPageType;

	private $_defaults = [
		Options::CREATE_PAGES  => true,
		Options::PARENT        => null,
		Options::LISTING_TYPE  => null,
		Options::PAGE_VARIANTS => self::INDIVIDUAL,
	];

	public function __construct(
		Page\Create $pageCreate,
		Page\Edit $pageEdit,
		Page\Loader $pageLoader,
		Page\ContentLoader $contentLoader,
		Page\ContentEdit $contentEdit,
		PageType\Collection $pageTypes,
		HeadingKeys $headingKeys,
		PageType\PageTypeInterface $listingPageType
	)
	{
		$this->_pageCreate      = $pageCreate;
		$this->_pageEdit        = $pageEdit;
		$this->_pageLoader      = $pageLoader;
		$this->_contentLoader   = $contentLoader;
		$this->_contentEdit     = $contentEdit;
		$this->_pageTypes       = $pageTypes;
		$this->_headingKeys     = $headingKeys;
		$this->_listingPageType = $listingPageType;
	}

	public function create(Product\Product $product, array $row, array $options = [])
	{
		$options = $options + $this->_defaults;

		if (empty($options[Options::CREATE_PAGES])) {
			return false;
		}

		$page = $this->_getNewProductPage($product, $this->_getParentPage($row, $options));
		$page->publishDateRange = new DateRange(new \DateTime);
		$this->_setProductPageContent($page, $product, $row, $options);
	}

	private function _getParentPage(array $row, array $options)
	{
		if (!$options[Options::PARENT]) {
			return null;
		}

		$grandparent    = $this->_pageLoader->getByID($options[Options::PARENT]);
		$grandParentChildren = $this->_pageLoader->getChildren($grandparent);

		$parentSiblings = [];

		foreach ($grandParentChildren as $page) {
			$parentSiblings[$page->title] = $page;
		}

		$key = $this->_headingKeys->getKey($options[Options::LISTING_TYPE]);
		$parentTitle = $row[$key];

		if (array_key_exists($parentTitle, $parentSiblings)) {
			return $parentSiblings[$parentTitle];
		}

		$parent = $this->_pageCreate->create(
			$this->_listingPageType,
			$parentTitle,
			$grandparent
		);

		return $parent;
	}

	private function _getNewProductPage(Product\Product $product, Page\Page $parent = null)
	{
		return $this->_pageCreate->create(
			$this->_pageTypes->get(self::PAGE_TYPE),
			$product->name,
			$parent
		);
	}

	private function _setProductPageContent(Page\Page $page, Product\Product $product, array $row, array $options)
	{
		$content = $this->_contentLoader->load($page);

		$contentData = $this->_getProductContent($product, $row, $options);

		$content = $this->_contentEdit->updateContent($contentData, $content);
		$this->_contentEdit->save($page, $content);

		return $page;
	}

	private function _getProductContent(Product\Product $product, array $row, array $options)
	{
		$content = [
			'description' => $row[$this->_headingKeys->getKey('description')],
			'product' => [
				'product' => $product->id
			]
		];

		if ($options[Options::PAGE_VARIANTS] !== self::INDIVIDUAL) {
			$variantKey = $options[Options::PAGE_VARIANTS];
			$content['product']['option'] = [
				'name'  => $row[$this->_headingKeys->getKey($variantKey)],
				'value' => $row[$this->_getVariantValueKey($variantKey)]
			];
		}

		return $content;
	}

	private function _getVariantValueKey($variantValueName)
	{
		if (!is_string($variantValueName)) {
			throw new \InvalidArgumentException('Variant value name must be a string, ' . gettype($variantValueName) . ' given');
		}

		$name = str_replace(HeadingKeys::VAR_NAME_PREFIX, HeadingKeys::VAR_VAL_PREFIX, $variantValueName);

		return $this->_headingKeys->getKey($name);
	}

}