<?php

namespace Message\Mothership\Ecommerce\Controller\Module\Dashboard;

use Message\Cog\Controller\Controller;

class OrdersFulfillmentTime extends Controller
{
	/**
	 *
	 *
	 * @return
	 */
	public function index()
	{
		$dataset = $this->get('statistics')->get('fulfillment.time');
		$average = $dataset->getAverage($dataset::WEEK);
		$average = round($average / (60 * 60));

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:number', [
			'label' => 'Orders fulfillment time (avg.)',
			'number' => [
				'value' => $average,
				'units' => 'hrs'
			]
		]);
	}
}