<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Cog\Controller\Controller;

class Picking extends Controller
{
	public function view($orderID, $documentID)
	{
		return $this->render('::fulfillment:picking:blank', array(
			'content' => $this->get('ecom.file.loader')->content($orderID, 'packing-slip', $documentID)
		));
	}
}