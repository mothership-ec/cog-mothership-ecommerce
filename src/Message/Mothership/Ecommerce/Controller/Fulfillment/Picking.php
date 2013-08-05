<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Cog\Controller\Controller;

class Picking extends Controller
{
	public function pickingSlip()
	{
		return $this->redirectToReferer();
	}
}