<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Mothership\Ecommerce\PageType\AbstractProduct as AbstractProductPage;
use Message\Mothership\Ecommerce\ProductPage;
use Message\Mothership\Ecommerce\Form\Product\CreateProductPages;

use Message\Cog\Field;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;

use Message\Mothership\Commerce\FieldType\Product as ProductField;
use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Product\Events as CommerceEvents;
use Message\Mothership\Commerce\Product\Upload;
use Message\Mothership\Commerce\Product\Upload\Exception\UploadFrontEndException;

use Message\Mothership\CMS\Page;
use Message\Mothership\CMS\Page\Event\ContentEvent;

class ProductPageListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return [
			// Product page events
			ContentEvent::CREATE => [
				['saveProductUnitRecords']
			],
			ContentEvent::EDIT => [
				['saveProductUnitRecords']
			],
			// Product upload events
			CommerceEvents::PRODUCT_UPLOAD_CREATE => [
				'createProductPageFromUpload',
			],
			CommerceEvents::UNIT_UPLOAD_CREATE => [
				'createUnitPageFromUpload',
			],
			CommerceEvents::PRODUCT_UPLOAD_COMPLETE => [
				'redirectToPageRecords',
			],
			ProductPage\Events::PRODUCT_PAGE_CREATE => [
				'createProductPageUploadRecord',
			],
			ProductPage\Events::PRODUCT_PARENT_PAGE_CREATE => [
				'createProductParentPage'
			],
		];
	}

	public function saveProductUnitRecords(ContentEvent $event)
	{
		$page = $event->getPage();

		if (!$page->getType() instanceof AbstractProductPage && $page->getType()->getName() !== 'product') {
			return false;
		}

		$productGroup = $event->getContent()->product;

		if (!$productGroup instanceof Field\Group) {
			return false;
		}

		$productField = $productGroup->product;

		if (!$productField instanceof ProductField) {
			return false;
		}

		$product = $productField->getProduct();

		if (!$product) {
			return false;
		}

		if (
			$productGroup->option &&
			null !== $productGroup->option->getValue()['name'] ||
			null !== $productGroup->option->getValue()['value']
		) {
			$options = [
				$productGroup->option->getValue()['name'] => $productGroup->option->getValue()['value']
			];
		} else {
			$options = [];
		}

		$units = $this->get('product.unit.loader')->getByProduct($product);

		foreach ($units as $key => $unit) {
			foreach ($options as $name => $value) {
				if (!$unit->hasOption($name) || $unit->getOption($name) != $value) {
					unset($units[$key]);
				}
			}
		}

		$this->get('product.page.unit_record.edit')->save($page, $product, $units);
	}

	public function createProductPageFromUpload(Upload\ProductCreateEvent $event)
	{
		$data    = $event->getFormData();
		$row     = $event->getRow();
		$product = $event->getProduct();

		if (empty($data[ProductPage\Options::CREATE_PAGES])) {
			return false;
		}

		if (empty($data[CreateProductPages::FIELD_NAME])) {
			return false;
		} else {
			$data = $data[CreateProductPages::FIELD_NAME];
		}

		if ($data[ProductPage\Options::PAGE_VARIANTS] !== ProductPage\Options::INDIVIDUAL) {
			return false;
		}

		$data[ProductPage\Options::CSV_PORT] = true;

		if ($data[ProductPage\Options::LISTING_TYPE] !== ProductPage\Options::SHOP) {
			$listingKey = $this->_services['product.upload.heading_keys']->getKey($data[ProductPage\Options::LISTING_TYPE]);

			if ('' === $row[$listingKey]) {
				throw new UploadFrontEndException('Page for \'' . $product->name . '\' (' . $product->id . ') could not be created as the field for \'' . $listingKey . '\' is empty');
			}
		}

		$this->_services['product.page.create']->create(
			$product,
			$data
		);
	}

	public function createUnitPageFromUpload(Upload\UnitCreateEvent $event)
	{
		$data    = $event->getFormData();
		$row     = $event->getRow();
		$product = $event->getProduct();
		$unit    = $event->getUnit();

		if (empty($data[ProductPage\Options::CREATE_PAGES])) {
			return false;
		}

		if (empty($data[CreateProductPages::FIELD_NAME])) {
			return false;
		} else {
			$data = $data[CreateProductPages::FIELD_NAME];
		}

		$variant = $data[ProductPage\Options::PAGE_VARIANTS];

		if ($variant === ProductPage\Options::INDIVIDUAL) {
			return false;
		}

		$data[ProductPage\Options::CSV_PORT] = true;

		$variantKey  = $this->_services['product.upload.heading_keys']->getKey($variant);
		$variantName = $row[$variantKey];
		$listingKey  = $this->_services['product.upload.heading_keys']->getKey($data[ProductPage\Options::LISTING_TYPE]);

		if ('' === $variantName) {
			throw new UploadFrontEndException('Page for \'' . $product->name . '\' (' . $product->id . ') could not be created as the field for \'' . $variantKey . '\' is empty');
		}
		elseif ('' === $row[$listingKey]) {
			throw new UploadFrontEndException('Page for \'' . $product->name . '\' (' . $product->id . ') could not be created as the field for \'' . $listingKey . '\' is empty');
		}

		$this->_services['product.page.create']->create(
			$product,
			$data,
			$unit,
			$variantName
		);
	}

	public function redirectToPageRecords(Upload\UploadCompleteEvent $event)
	{
		$event->setRoute('ms.product.product_upload.confirm');
	}

	public function createProductPageUploadRecord(ProductPage\ProductPageCreateEvent $event)
	{
		if ($event->isCsvPort()) {
			$this->get('product.page.upload_record.create')->create(
				$this->get('product.page.upload_record.builder')->build(
					$event->getPage(),
					$event->getProduct(),
					$event->getUnit()
				)
			);
		}
	}

	public function createProductParentPage(ProductPage\ParentPageCreateEvent $event)
	{
		$page = $this->_services['cms.page.create']->create(
			$event->getPageType(),
			$event->getPageName(),
			$event->getParent()
		);

		$event->setPage($page);
	}
}