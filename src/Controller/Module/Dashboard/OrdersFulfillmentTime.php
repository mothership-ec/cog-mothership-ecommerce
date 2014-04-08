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

			$awaitingData   = $this->get('db.query')->run("SELECT item_id, created_at FROM order_item_status WHERE status_code = 0 AND created_at > (UNIX_TIMESTAMP() - (60 * 60 * 24 * 7)) ORDER BY created_at DESC");
			$dispatchedData = $this->get('db.query')->run("SELECT item_id, created_at FROM order_item_status WHERE status_code = 1000 AND created_at > (UNIX_TIMESTAMP() - (60 * 60 * 24 * 7)) ORDER BY created_at DESC");

			$total = 0;
			$count = 0;

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

			$intersect = array_intersect_key($dispatched, $awaiting);

			$sum = [];
			foreach ($intersect as $id => $v) {
				$sum[$id] = $dispatched[$id] - $awaiting[$id];
			}

			if (count($sum)) {
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

		return $this->render('::modules:dashboard:orders-fulfillment-time', $data);
	}
}