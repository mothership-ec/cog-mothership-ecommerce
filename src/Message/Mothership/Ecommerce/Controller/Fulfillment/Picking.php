<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\OrderItemStatuses;

class Picking extends Controller
{
	public function printSlip()
	{
		$loader = $this->get('order.loader');
		$orders = $loader->getByCurrentItemStatus(OrderItemStatuses::HOLD);
		$form = $this->get('form.orders.checkbox')->build($orders, 'new');

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$printOrders = array();
			foreach ($data['choices'] as $orderID) {
				$this->_updateItemStatus($orderID, OrderItemStatuses::PRINTED);
				$printOrders[] = $loader->getByID($orderID);
			}
			return $this->render('::fulfillment:picking:print', array(
				'orders' => $printOrders,
			));
		}

		return $this->redirectToReferer();
	}
}