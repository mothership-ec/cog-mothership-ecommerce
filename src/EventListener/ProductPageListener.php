<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Mothership\Ecommerce\ProductPage\Options;

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

		if ($data[Options::PAGE_VARIANTS] !== Options::INDIVIDUAL) {
			return false;
		}

		$this->_services['product.page.create']->create(
			$event->getProduct(),
			$event->getFormData()
		);
	}

	public function createUnitPageFromUpload(Upload\UnitCreateEvent $event)
	{
		$data = $event->getFormData();
		$row  = $event->getRow();

		$variant = $data[Options::PAGE_VARIANTS];

		if ($variant === Options::INDIVIDUAL) {
			throw new \LogicException('Trying to create unit pages when user has selected to create pages for individual products only');
		}

		$variantName = 	$row[$this->_services['product.upload.heading_keys']->getKey($variant)];

		$this->_services['product.page.create']->create(
			$event->getProduct(),
			$event->getFormData(),
			$event->getUnit(),
			$variantName
		);
	}
}