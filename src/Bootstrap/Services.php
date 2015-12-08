<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Mothership\Ecommerce;
use Message\Mothership\Ecommerce\Gateway;
use Message\Mothership\Ecommerce\Statistic;
use Message\Cog\Bootstrap\ServicesInterface;
use Message\Mothership\Ecommerce\OrderItemStatuses;
use Message\Mothership\Commerce\Order\Status\Status;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$this->addOrderStatuses($services);
		$this->registerEmails($services);
		$this->registerStatisticsDatasets($services);
		$this->registerPaymentGateways($services);

		$services['form.orders.checkbox'] = $services->factory(function($sm) {
			return new \Message\Mothership\Ecommerce\Form\Orders($sm);
		});

		/**
		 * @deprecated
		 */
		$services['form.pickup'] = $services->factory(function($sm) {
			return new \Message\Mothership\Ecommerce\Form\Pickup($sm);
		});

		$services['form.fulfillment.pickup'] = $services->factory(function($c) {
			return new \Message\Mothership\Ecommerce\Form\Fulfillment\Pickup($c['order.dispatch.loader']);
		});

		$services['file.packing_slip'] = $services->factory(function($sm) {
			return new \Message\Mothership\Ecommerce\File\PackingSlip($sm);
		});

		$services['checkout.hash'] = function($c) {
			return new \Message\Cog\Security\Hash\SHA1($c['security.salt']);
		};

		// Add payments logger
		$services['log.payments'] = function($c) {
			$logger = new \Monolog\Logger('payments');

			return $logger;
		};

		// @todo move to commerce, where address is
		$services['address.form'] = $services->factory(function($sm) {
			return new \Message\Mothership\Ecommerce\Form\AddressForm($sm);
		});

		$services['checkout.form.addresses'] = $services->factory(function($sm) {
			return new \Message\Mothership\Ecommerce\Form\CheckoutAddressesForm($sm);
		});

		$services['checkout.form.register'] = $services->factory(function($sm) {
			return new \Message\Mothership\Ecommerce\Form\CheckoutRegisterForm($sm);
		});

		$services['checkout.form.confirm'] = function ($c) {
			return new \Message\Mothership\Ecommerce\Form\CheckoutConfirmForm($c['gateways'], $c['translator']);
		};

		$services['product.form.upload_confirm'] = $services->factory(function($c) {
			return new \Message\Mothership\Ecommerce\Form\Product\CsvUploadConfirm(
				$c['routing.generator'],
				$c['product.form.upload_create_product_pages'],
				(bool) $c['cfg']->shop->shopParentPage
			);
		});

		$services['product.form.upload_create_product_pages'] = $services->factory(function($c) {
			return new \Message\Mothership\Ecommerce\Form\Product\CreateProductPages(
				$c['translator'],
				$c['cms.page.loader'],
				$c['http.session'],
				$c['product.page.variant_name_crawler'],
				$c['product.page.brand_validator'],
				$c['cfg']->shop->shopParentPage
			);
		});

		$services['product.form.product_page_publish'] = $services->factory(function($c) {
			return new \Message\Mothership\Ecommerce\Form\Product\ProductPagePublish;
		});

		$services['product.form.product_page_create'] = $services->factory(function($c) {
			return new \Message\Mothership\Ecommerce\Form\Product\ProductPageCreateSingle($c['cms.page.loader'], $c['product.option.loader']);
		});

		$services['product.page.unit_record.edit'] = $services->factory(function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\UnitRecord\Edit($c['db.transaction']);
		});

		$services['product.page.create'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\Create(
				$c['cms.page.create'],
				$c['cms.page.edit'],
				$c['cms.page.loader'],
				$c['cms.page.content_loader'],
				$c['cms.page.content_edit'],
				$c['cms.page.types'],
				$c['product.page_type.listing'],
				$c['product.page.create_dispatcher'],
				$c['product.page.parent_create_dispatcher'],
				$c['product.page.exists'],
				$c['product.page_type.mapping']
			);
		};

		$services['product.page.exists'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\Exists($c['db.query']);
		};

		$services['product.page.upload_record.builder'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\UploadRecord\Builder;
		};

		$services['product.page.upload_record.create'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\UploadRecord\Create($c['db.transaction']);
		};

		$services['product.page.upload_record.loader'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\UploadRecord\Loader($c['db.query']);
		};

		$services['product.page.upload_record.edit'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\UploadRecord\Edit($c['db.transaction']);
		};

		$services['product.page_type.listing'] = function($c) {
			return null;
		};

		$services['product.page_type.mapping'] = function($c) {
			return [];
		};

		$services['product.page.create_dispatcher'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\ProductPageCreateEventDispatcher($c['event.dispatcher']);
		};

		$services['product.page.parent_create_dispatcher'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\ParentPageCreateEventDispatcher($c['event.dispatcher']);
		};

		$services['product.page.variant_name_crawler'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\UploadData\VariantNameCrawler($c['product.upload.heading_keys']);
		};

		$services['product.page.brand_validator'] = function($c) {
			return new \Message\Mothership\Ecommerce\ProductPage\UploadData\BrandValidator($c['product.upload.heading_keys']);
		};

		// Override from commerce to allow the use of page mappings.
		$services['product.page_mapper.simple'] = function($c) {
			$mapper = new \Message\Mothership\Commerce\ProductPageMapper\SimpleMapper(
				$c['db.query'],
				$c['cms.page.loader'],
				$c['cms.page.authorisation'],
				$c['product.loader'],
				$c['product.unit.loader']
			);

			$mapper->setValidFieldNames('product');
			$mapper->setValidGroupNames(null);
			$mapper->setValidPageTypes(
				array_merge(
					['product'],
					array_values($c['product.page_type.mapping'])
				)
			);

			return $mapper;
		};
	}

	public function addOrderStatuses($services)
	{
		$services->extend('order.statuses', function($statuses) {
			$statuses->add(new Status(OrderItemStatuses::RETURNED, 'Fully returned'));

			return $statuses;
		});

		$services->extend('order.item.statuses', function($statuses) {
			$statuses
				->add(new Status(OrderItemStatuses::AWAITING_PAYMENT, 'Awaiting Payment'))
				->add(new Status(OrderItemStatuses::HOLD,             'On Hold'))
				->add(new Status(OrderItemStatuses::PRINTED,          'Printed'))
				->add(new Status(OrderItemStatuses::PICKED,           'Picked'))
				->add(new Status(OrderItemStatuses::PACKED,           'Packed'))
				->add(new Status(OrderItemStatuses::POSTAGED,         'Postaged'))
				->add(new Status(OrderItemStatuses::RETURN_WAITING,   'Waiting to Receive Returned Item'))
				->add(new Status(OrderItemStatuses::RETURN_ARRIVED,   'Returned Item Arrived'))
				->add(new Status(OrderItemStatuses::RETURNED,         'Returned'));

			return $statuses;
		});
	}

	public function registerEmails($services)
	{
		$services['mail.factory.order.confirmation'] = $services->factory(function($c) {
			$factory = new \Message\Cog\Mail\Factory($c['mail.message']);

			$factory->requires('order', 'payments');

			$appName = $c['cfg']->app->name;

			$factory->extend(function($factory, $message) use ($appName) {
				$message->setTo($factory->order->user->email);
				$message->setSubject(sprintf('Your %s order confirmation - %d', $appName, $factory->order->orderID));
				$message->setView('Message:Mothership:Ecommerce::mail:order:confirmation', array(
					'order'       => $factory->order,
					'payments'    => $factory->payments,
					'companyName' => $appName,
				));
			});

			return $factory;
		});
	}

	public function registerStatisticsDatasets($services)
	{
		$services->extend('statistics', function($statistics, $c) {
			$statistics->add(new Statistic\FulfillmentTime($c['db.query'], $c['statistics.counter'], $c['statistics.range.date']));

			return $statistics;
		});
	}

	/**
	 * Register the available payment gateways. Construct each gateway adapter,
	 * add to the gateway collection and define the default gateway service.
	 *
	 * @param  \Message\Cog\Bootstrap\Services $services
	 */
	public function registerPaymentGateways($services)
	{
		// Local payments adapter
		$services['gateway.adapter.local-payment'] = function($c) {
			return new Gateway\LocalPayment\Gateway;
		};

		// Zero payment adapter
		$services['gateway.adapter.zero-payment'] = function($c) {
			return new Gateway\ZeroPayment\Gateway;
		};

		// Gateway collection
		$services['gateway.collection'] = function($c) {
			return new Gateway\Collection([
				$c['gateway.adapter.local-payment'],
				$c['gateway.adapter.zero-payment'],
			]);
		};

		// Validation collection
		$services['gateway.validation'] = $services->factory(function($c) {
			return new Gateway\Validation\Collection;
		});

		$services['gateway.validation.address'] = $services->factory(function($c) {
			return new Gateway\Validation\AddressValidator(
				$c['country.list'],
				$c['state.list']
			);
		});

		// Active gateway service
		// @deprecated call `gateways` instead and select the appropriate gateway
		$services['gateway'] = function($c) {
			$gateway = $c['cfg']->payment->gateway;

			if (is_array($gateway)) {
				$gateway = array_shift($gateway);
			}

			return $c['gateway.collection']->get($gateway);
		};

		$services['gateways'] = function ($c) {
			$gateways = $c['cfg']->payment->gateway;

			if (!is_array($gateways)) {
				$gateways = [$gateways];
			}

			$collection = $c['gateway.collection'];

			array_walk($gateways, function (&$gateway) use ($collection) {
				$gateway = $collection->get($gateway);
			});

			$gateways = new Gateway\Collection($gateways);
			$gateways->setSort(null);

			return $gateways;
		};

		$services['payment.gateway.loader'] = function ($c) {
			return new Ecommerce\Payment\PaymentGatewayRecordLoader($c['payment.loader'], $c['gateway.collection'], $c['db.query.builder.factory']);
		};

		$services['payment.gateway.edit'] = function ($c) {
			return new Ecommerce\Payment\PaymentGatewayRecordEdit($c['db.query']);
		};
	}
}
