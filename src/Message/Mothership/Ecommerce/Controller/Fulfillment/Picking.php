<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Cog\Controller\Controller;

class Picking extends Controller
{
	public function view($orderID, $documentID)
	{
		$order = $this->get('order.loader')->getByID($orderID);
		$document = $this->get('order.document.loader')->getByID($documentID, $order);

		return $this->render('::fulfillment:picking:blank', array(
			'content' => file_get_contents($document->file)
		));
	}
}