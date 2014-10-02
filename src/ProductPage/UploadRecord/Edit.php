<?php

namespace Message\Mothership\Ecommerce\ProductPage\UploadRecord;

use Message\Cog\DB;

class Edit implements DB\TransactionalInterface
{
	private $_transaction;
	private $_transOverride = false;

	public function __construct(DB\Transaction $transaction)
	{
		$this->_transaction = $transaction;
	}

	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_transaction   = $transaction;
		$this->_transOverride = true;

		return $this;
	}

	public function save(UploadRecord $record)
	{
		$this->_transaction->add("
			UPDATE
				product_page_upload_record
			SET
				product_id   = :productID?i,
				unit_id      = :unitID?in,
				confirmed_at = :confirmedAt?dn,
				confirmed_by = :confirmedBy?in
			WHERE
				page_id = :pageID?i
		", [
			'productID'   => $record->getProductID(),
			'unitID'      => $record->getUnitID(),
			'confirmedAt' => $record->getConfirmedAt(),
			'confirmedBy' => $record->getConfirmedBy(),
			'pageID'      => $record->getPageID(),
		]);

		if (!$this->_transOverride) {
			$this->_transaction->commit();
		}
	}
}