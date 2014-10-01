<?php

namespace Message\Mothership\Ecommerce\ProductPage\UploadRecord;

use Message\Cog\DB\Query;

class Loader
{
	private $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function getUnconfirmed()
	{
		$records = $this->_query->run("
			SELECT
				record.page_id AS pageID,
				product_id AS productID,
				page.title AS pageTitle,
				unit_id AS unitID
			FROM
				product_page_upload_record AS record
			LEFT JOIN
				page
			ON
				(record.page_id = page.page_id)
			WHERE
				confirmed_at IS NULL
		")->bindTo('Message\\Mothership\\Ecommerce\\ProductPage\\UploadRecord\\UploadRecord');

		return $records;
	}
}