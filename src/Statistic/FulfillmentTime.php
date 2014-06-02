<?php

namespace Message\Mothership\Ecommerce\Statistic;

use Message\Mothership\ControlPanel\Statistic\AbstractDataset;

class FulfillmentTime extends AbstractDataset
{
	public function getName()
	{
		return 'fulfillment.time';
	}

	public function getPeriodLength()
	{
		return static::DAILY;
	}

	public function rebuild()
	{
		$this->_query->run("
			DELETE FROM
				statistic
			WHERE
				dataset = 'fulfillment.time';
		");

		$this->_query->run("
			INSERT INTO
				statistic
			SELECT
				'fulfillment.time',
				CONCAT('fulfillment.time.', os.order_id),
				ois.created_at,
				ois.created_at - os.created_at,
				UNIX_TIMESTAMP(NOW())
			FROM
				order_summary os
			LEFT JOIN
				order_item_status ois ON (ois.order_id = os.order_id)
			WHERE
				ois.status_code = 1000
			GROUP BY
				os.order_id;
		");

		if (! $this->_transOverriden) {
			$this->_query->commit();
		}
	}
}