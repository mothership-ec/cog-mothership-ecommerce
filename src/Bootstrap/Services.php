<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Mothership\Ecommerce\OrderItemStatuses;

use Message\Mothership\Commerce\Order\Status\Status;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$this->addOrderStatuses($services);
		$this->registerEmails($services);
		$this->registerPaymentGateways($services);

		$services['form.orders.checkbox'] = $services->factory(function($sm) {
			return new \Message\Mothership\Ecommerce\Form\Orders($sm);
		});

		$services['form.pickup'] = $services->factory(function($sm) {
			return new \Message\Mothership\Ecommerce\Form\Pickup($sm);
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

			if (in_array($c['env'], array('live', 'staging'))) {
				$logger->pushHandler(
					new \Monolog\Handler\HipChatHandler(
						'fa33f6b754f4a4663cc3d7efd025bb',
						354103,
						$c['environment']->getWithInstallation(),
						true,
						$logger::NOTICE,
						true
					)
				);
			}

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

	public function registerPaymentGateways($services)
	{
		$services['gateway.adapter.sagepay'] = function($c) {
			return new Gateway\Sagepay\Gateway;
		};

		$services['gateway.collection'] = function($c) {
			return new Gateway\Collection([
				$c['gateway.adapter.sagepay']
			]);
		};

		$services['gateway'] = function($c) {
			return $c['gateway.collection']->get('sagepay');
		};
	}
}
