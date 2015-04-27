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
		);
	}

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

	public function redirectToHome(Order\Event\UpdateFailedEvent $event)
	{
		$page = $this->get('cms.page.loader')->getHomepage();

		$redirectEvent = new Page\Event\SetResponseForRenderEvent($page, $page->getContent());
		$redirectEvent->setResponse(new RedirectResponse($page->slug));

		$this->get('http.session')->getFlashBag()->add('error', $this->get('translator')->trans('ms.ecom.error.basket'));

		$this->get('event.dispatcher')->dispatch(Page\Event\Event::RENDER_SET_RESPONSE, $redirectEvent);
	}
}