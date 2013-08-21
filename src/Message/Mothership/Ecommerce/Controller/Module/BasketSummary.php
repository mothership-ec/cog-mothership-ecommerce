<?php

namespace Message\Mothership\Ecommerce\Controller\Module;

use Message\Cog\Controller\Controller;

class BasketSummary extends Controller
{
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

		return $this->render('Message:Mothership:Ecommerce::Module:basket-summary', array(
			'order'	  => $basket->getOrder(),
			'basket'  => $basketDisplay,
			'shippingOptions' => $this->get('shipping.methods')
		));
	}
}