<?php

namespace Message\Mothership\Ecommerce\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order;
use Message\Mothership\CMS\Page;
use Message\Cog\HTTP\RedirectResponse;

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
			Order\Events::CREATE_COMPLETE => [
				['sendOrderConfirmationMail'],
			],
			Order\Events::UPDATE_FAILED => [
				['redirectToHome']
			],
			Order\Events::ORDER_CANCEL_REFUND => [
				['setOrderRefundController']
			],
			Order\Events::ITEM_CANCEL_REFUND => [
				['setItemRefundController']
			],
		);
	}

	/**
	 * @param Order\Event\Event $event
	 */
	public function sendOrderConfirmationMail(Order\Event\Event $event)
	{
		$order = $event->getOrder();
		$merchant = $this->get('cfg')->merchant;

		if ($order->type == 'web') {
			$payments = $this->get('order.payment.loader')->getByOrder($order);

			$factory = $this->get('mail.factory.order.confirmation')
				->set('order', $order)
				->set('payments', $payments);

			$this->get('mail.dispatcher')->send($factory->getMessage());
		}
	}

	/**
	 * @param Order\Event\UpdateFailedEvent $event
	 */
	public function redirectToHome(Order\Event\UpdateFailedEvent $event)
	{
		$page = $this->get('cms.page.loader')->getHomepage();

		$redirectEvent = new Page\Event\SetResponseForRenderEvent($page, $page->getContent());
		$redirectEvent->setResponse(new RedirectResponse($page->slug));

		$this->get('event.dispatcher')->dispatch(Page\Event\Event::RENDER_SET_RESPONSE, $redirectEvent);
	}

	/**
	 * @param Order\Event\CancelEvent $event
	 */
	public function setOrderRefundController(Order\Event\CancelEvent $event)
	{
		$this->_setRefundController($event, 'order');
	}

	/**
	 * @param Order\Event\CancelEvent $event
	 */
	public function setItemRefundController(Order\Event\CancelEvent $event)
	{
		$this->_setRefundController($event, 'item');
	}

	/**
	 * Set controller and parameters to process refund via gateway. Can configure cancellation for
	 * orders or individual items by giving 'order' or 'item' as the second parameter
	 *
	 * @param Order\Event\CancelEvent $event
	 * @param $type
	 * @throws \LogicException                  Throws exception if there are no payments to refund
	 */
	private function _setRefundController(Order\Event\CancelEvent $event, $type)
	{
		$types = ['order', 'item'];
		if (!in_array($type, $types)) {
			throw new \LogicException('Invalid refund type, must be in array: ' . implode(', ', $types));
		}

		$paymentReference = null;
		$gateway = null;

		foreach ($event->getOrder()->payments as $payment) {
			$gateway = $this->get('payment.gateway.loader')->getGatewayByPayment($payment->payment);
			$paymentReference = $payment->reference;
			break;
		}

		if (!$gateway) {
			throw new \LogicException('Could not load gateway, no payments to refund');
		}

		$controller = 'Message:Mothership:Commerce::Controller:Order:Cancel:Refund';

		$event->setControllerReference($gateway->getRefundControllerReference());
		$event->setParams([
			'payable' => $event->getRefund(),
			'reference' => $paymentReference,
			'stages' => [
				'failure' => $controller . '#' . $type . 'Failure',
				'success' => $controller . '#' . $type . 'Success',
			],
		]);
	}
}