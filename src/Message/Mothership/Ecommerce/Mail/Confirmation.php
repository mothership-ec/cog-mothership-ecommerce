<?php

namespace Message\Mothership\Ecommerce\Mail;

use Message\Cog\Mail\Factory;
use Message\Mothership\Commerce\Order\Order;

class Confirmation extends Factory {

	public function build(Order $order, array $payments, $companyName)
	{
		$this->_message->setTo($order->user->email);
		$this->_message->setSubject('Your ' . $companyName . ' order confirmation - ' . $order->orderID);
		$this->_message->setView('Message:Mothership:Ecommerce::mail:order:confirmation', array(
			'order'    => $order,
			'payments' => $payments,
			'merchant' => $companyName,
		));

		return $this->_message;
	}

}