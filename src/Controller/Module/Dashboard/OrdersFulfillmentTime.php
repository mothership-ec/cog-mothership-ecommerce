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
		$average = $dataset->range->getAverage($dataset->range->getWeekAgo());

		$averageHours = round($average / (60*60), 1);

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:number', [
			'label' => 'Orders fulfillment time (avg.)',
			'number' => [
				'value' => $averageHours,
				'units' => 'hrs'
			]
		]);
	}
}