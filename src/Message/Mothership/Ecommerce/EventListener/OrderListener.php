<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order;

/**
 * Checkout event listener for deciding where where to route the user
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
		return array(Order\Events::CREATE_END => array(
			array('sendOrderConfirmationMail')
		));
	}

	public function sendOrderConfirmationMail(Order\Event\Event $event)
	{
		$order = $event->getOrder();

		$mail = $this->get('mail.message');
		$mail->setTo($order->user->email);
		$mail->setSubject('Order Confirmation');
		$mail->setView('Message:Mothership:Ecommerce::mail:order:confirmation');

		$dispatcher = $this->get('mail.dispatcher');
		$dispatcher->send($mail);
	}
}