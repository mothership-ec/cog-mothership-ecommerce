<?php

namespace Message\Mothership\Ecommerce\Controller\Module\Dashboard;

use Message\Cog\Controller\Controller;

class OrdersFulfillmentTime extends Controller
{
	const CACHE_KEY = 'dashboard.module.orders-fulfillment-time';
	const CACHE_TTL = 3600;

	/**
	 *
	 *
	 * @return
	 */
	public function index()
	{
		if (false === $data = $this->get('cache')->fetch(self::CACHE_KEY)) {

			// Get the all the awaiting and dispatched items
			$awaitingData   = $this->get('db.query')->run("
				SELECT item_id, created_at
				FROM order_item_status
				WHERE status_code = 0
				AND created_at > (UNIX_TIMESTAMP() - (60 * 60 * 24 * 7))
				ORDER BY created_at DESC
			");

			$dispatchedData = $this->get('db.query')->run("
				SELECT item_id, created_at
				FROM order_item_status
				WHERE status_code = 1000
				AND created_at > (UNIX_TIMESTAMP() - (60 * 60 * 24 * 7))
				ORDER BY created_at DESC
			");

			$total = 0;
			$count = 0;

			// Convert these result sets into arrays indexed by the item id
			$dispatched = $awaiting = [];

			foreach ($awaitingData as $a) {
				if (!isset($awaiting[$a->item_id])) {
					$awaiting[$a->item_id] = $a->created_at;
				}
			}

			foreach ($dispatchedData as $d) {
				if (!isset($dispatched[$d->item_id])) {
					$dispatched[$d->item_id] = $d->created_at;
				}
			}

			// Create an intersect where the item is in both dispatched and
			// awaiting
			$intersect = array_intersect_key($dispatched, $awaiting);

			// Get the difference in time for each item between being placed by
			// the customer and being dispatched
			$sum = [];
			foreach ($intersect as $id => $v) {
				$sum[$id] = $dispatched[$id] - $awaiting[$id];
			}

			if (count($sum)) {
				// Get the average dispatch time in hours
				$time = floor((array_sum($sum) / count($sum)) / (60 * 60));
			} else {
				$time = 0;
			}
			$units = 'hrs';

			$data = [
				'time' => $time,
				'units' => $units
			];

			$this->get('cache')->store(self::CACHE_KEY, $data, self::CACHE_TTL);
		}

		return $this->render('Message:Mothership:Ecommerce::module:dashboard:orders-fulfillment-time', $data);
	}
}