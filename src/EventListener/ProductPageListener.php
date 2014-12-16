<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Mothership\Ecommerce\ProductPage;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Product\Events as CommerceEvents;
use Message\Mothership\Commerce\Product\Upload;
use Message\Mothership\Commerce\Product\Upload\Exception\UploadFrontEndException;


class ProductPageListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return [
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

	public function createProductPageFromUpload(Upload\ProductCreateEvent $event)
	{
		$data    = $event->getFormData();
		$row     = $event->getRow();
		$product = $event->getProduct();

		if (!$data[ProductPage\Options::CREATE_PAGES]) {
			return false;
		}

		if ($data[ProductPage\Options::PAGE_VARIANTS] !== ProductPage\Options::INDIVIDUAL) {
			return false;
		}

		$data[ProductPage\Options::CSV_PORT] = true;

		$listingKey  = $this->_services['product.upload.heading_keys']->getKey($data[ProductPage\Options::LISTING_TYPE]);

		if ('' === $row[$listingKey]) {
			throw new UploadFrontEndException('Page for \'' . $product->name . '\' (' . $product->id . ') could not be created as the field for \'' . $listingKey . '\' is empty');
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

		if (!$data[ProductPage\Options::CREATE_PAGES]) {
			return false;
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