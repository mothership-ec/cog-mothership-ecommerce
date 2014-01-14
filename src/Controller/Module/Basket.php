<?php

namespace Message\Mothership\Ecommerce\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Mothership\CMS\Page\Content;
use Message\Cog\Event\Event;

class Basket extends Controller
{
	protected $_product;

	public function index()
	{
		$basket = $this->get('basket');
		$unit = $this->get('product.unit.loader')->includeInvisible(true)->includeOutOfStock(true)->getByID(126);

		$basketDisplay = array();
		foreach ($basket->getOrder()->items as $item) {
			if (!isset($basketDisplay[$item->unitID]['quantity'])) {
				$basketDisplay[$item->unitID]['quantity'] = 0;
			}

			$basketDisplay[$item->unitID]['item']      = $item;
			$basketDisplay[$item->unitID]['quantity'] += 1;
		}

		return $this->render('Message:Mothership:Ecommerce::Module:basket', array(
			'order'	  => $basket->getOrder(),
			'basket'  => $basketDisplay,
		));
	}

	public function emptyBasket()
	{
		$this->get('http.session')->remove('basket.order');

		// Dispatch the edit event
		$this->get('event.dispatcher')->dispatch(
			\Message\Mothership\Ecommerce\Event::EMPTY_BASKET,
			new Event
		);

		return $this->redirectToReferer();
	}

}