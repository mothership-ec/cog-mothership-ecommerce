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

		$services['form.orders.checkbox'] = function($sm) {
			return new \Message\Mothership\Ecommerce\Form\Orders($sm);
		};

		$services['form.pickup'] = function($sm) {
			return new \Message\Mothership\Ecommerce\Form\Pickup($sm);
		};

		$services['file.packing_slip'] = function($sm) {
			return new \Message\Mothership\Ecommerce\File\PackingSlip($sm);
		};

		$services['ecom.file.loader'] = function($sm) {
			return new \Message\Mothership\Ecommerce\File\Loader(
				$sm['db.query']
			);
		};

		$services['checkout.hash'] = $services->share(function($c) {
			return new \Message\Cog\Security\Hash\SHA1($c['security.salt']);
		});

		// Add payments logger
		$services['log.payments'] = $services->share(function($c) {
			$logger = new \Monolog\Logger('payments');

			if (in_array($c['env'], array('live', 'staging'))) {
				$logger->pushHandler(
					new \Monolog\Handler\HipChatHandler(
						'fa33f6b754f4a4663cc3d7efd025bb',
						354103,
						$c['environment']->getWithInstallation(),
						true,
						$logger::DEBUG,
						true
					)
				);
			}

			return $logger;
		});

		// Service to find pages associated with a product
		$services['product.page_finder'] = function($c) {
			$finder = new \Message\Mothership\Ecommerce\Finder\ProductPageFinder(
				$c['db.query'],
				$c['cms.page.loader'],
				$c['cms.page.authorisation']
			);

			return $finder;
		};

		$services['templating.twig.environment'] = $services->share(
			$services->extend('templating.twig.environment', function($twig, $c) {
				$twig->addExtension(new \Message\Mothership\Ecommerce\Templating\ProductPageFinderTwigExtension(
					$c['product.page_finder']
				));

				return $twig;
			})
		);

		// Service to find products associated with a page
		$services['cms.page.product_finder'] = function($c) {
			$finder = new \Message\Mothership\Ecommerce\Finder\PageProductFinder(
				$c['db.query'],
				$c['product.loader'],
				$c['product.unit.loader']
			);

			return $finder;
		};

		$services['templating.twig.environment'] = $services->share(
			$services->extend('templating.twig.environment', function($twig, $c) {
				$twig->addExtension(new \Message\Mothership\Ecommerce\Templating\PageProductFinderTwigExtension(
					$c['cms.page.product_finder']
				));

				return $twig;
			})
		);
	}

	public function addOrderStatuses($services)
	{
		$services['order.statuses']
			->add(new Status(OrderItemStatuses::RETURNED, 'Fully returned'));

		$services['order.item.statuses']
			->add(new Status(OrderItemStatuses::AWAITING_PAYMENT, 'Awaiting Payment'))
			->add(new Status(OrderItemStatuses::HOLD,             'On Hold'))
			->add(new Status(OrderItemStatuses::PRINTED,          'Printed'))
			->add(new Status(OrderItemStatuses::PICKED,           'Picked'))
			->add(new Status(OrderItemStatuses::PACKED,           'Packed'))
			->add(new Status(OrderItemStatuses::POSTAGED,         'Postaged'))
			->add(new Status(OrderItemStatuses::RETURN_WAITING,   'Waiting to Receive Returned Item'))
			->add(new Status(OrderItemStatuses::RETURN_ARRIVED,   'Returned Item Arrived'))
			->add(new Status(OrderItemStatuses::RETURNED,         'Returned'));
	}

	public function registerEmails($services)
	{
		$services['mail.factory.order.confirmation'] = function($c) {
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
		};
	}
}