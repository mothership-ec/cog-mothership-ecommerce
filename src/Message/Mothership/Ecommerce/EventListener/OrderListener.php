<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order;

/**
 * Order event listener.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class OrderListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			Order\Events::CREATE_COMPLETE => array(
				array('adjustStock'),
				array('sendOrderConfirmationMail'),
			)
		);
	}

	public function sendOrderConfirmationMail(Order\Event\Event $event)
	{
		$order = $event->getOrder();

		if ($order->type == 'web') {
			$payments = $this->get('order.payment.loader')->getByOrder($order);

			$mail = $this->get('mail.message');
			$mail->setTo($order->user->email);
			$mail->setSubject('Order Confirmation');
			$mail->setView('Message:Mothership:Ecommerce::mail:order:confirmation', array(
				'order' => $order,
				'payments' => $payments,
				'merchant' => $this->get('cfg')->merchant,
			));

			$dispatcher = $this->get('mail.dispatcher');
			$dispatcher->send($mail);
		}
	}

	public function adjustStock(Order\Event\Event $event)
	{
		$order = $event->getOrder();
		$stockManager = $this->get('stock.manager');

		$stockManager->setReason($this->get('stock.movement.reasons')->get('new_order'));
		$stockManager->setNote(sprintf('Order #%s', $order->id));
		$stockManager->setAutomated(true);

		foreach($order->getItems() as $item) {
			$stockManager->decrement($item->getUnit(), $item->stockLocation);
		}

		$stockManager->commit();
	}
}