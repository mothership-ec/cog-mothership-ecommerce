<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Mothership\Ecommerce\ProductPage;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Product\Events as CommmerceEvents;
use Message\Mothership\Commerce\Product\Upload;
use Message\Mothership\Commerce\Product\Upload\Exception\UploadFrontEndException;


class ProductPageListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return [
			CommmerceEvents::PRODUCT_UPLOAD_CREATE => [
				'createProductPageFromUpload',
			],
			CommmerceEvents::UNIT_UPLOAD_CREATE => [
				'createUnitPageFromUpload',
			],
			ProductPage\Events::PRODUCT_PAGE_CREATE => [
				'createProductPageUploadRecord',
			],
		];
	}

	public function createProductPageFromUpload(Upload\ProductCreateEvent $event)
	{
		$data    = $event->getFormData();
		$row     = $event->getRow();
		$product = $event->getProduct();

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

		$variant = $data[ProductPage\Options::PAGE_VARIANTS];

		if ($variant === ProductPage\Options::INDIVIDUAL) {
			throw new \LogicException('Trying to create unit pages when user has selected to create pages for individual products only');
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

	public function createProductPageUploadRecord(ProductPage\ProductPageCreateEvent $event)
	{
		if ($event->isCsvPort()) {
			$this->get('product.page.upload_record_create')->create(
				$this->get('product.page.upload_record_builder')->build(
					$event->getPage(),
					$event->getProduct(),
					$event->getUnit()
				)
			);
		}
	}
}