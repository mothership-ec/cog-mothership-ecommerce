<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Mothership\Ecommerce\ProductPage;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Product\Events;
use Message\Mothership\Commerce\Product\Upload;


class ProductPageListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return [
			Events::PRODUCT_UPLOAD_CREATE => [
				'createProductPageFromUpload',
			],
			Events::UNIT_UPLOAD_CREATE => [
				'createUnitPageFromUpload',
			]
		];
	}

	public function createProductPageFromUpload(Upload\ProductCreateEvent $event)
	{
		$data = $event->getFormData();

		if ($data[ProductPage\Options::PAGE_VARIANTS] !== ProductPage\Options::INDIVIDUAL) {
			return false;
		}

		$data[ProductPage\Options::CSV_PORT] = true;

		$this->_services['product.page.create']->create(
			$event->getProduct(),
			$data
		);
	}

	public function createUnitPageFromUpload(Upload\UnitCreateEvent $event)
	{
		$data = $event->getFormData();
		$row  = $event->getRow();

		$variant = $data[ProductPage\Options::PAGE_VARIANTS];

		if ($variant === ProductPage\Options::INDIVIDUAL) {
			throw new \LogicException('Trying to create unit pages when user has selected to create pages for individual products only');
		}

		$data[ProductPage\Options::CSV_PORT] = true;
		$variantName = 	$row[$this->_services['product.upload.heading_keys']->getKey($variant)];

		$this->_services['product.page.create']->create(
			$event->getProduct(),
			$data,
			$event->getUnit(),
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