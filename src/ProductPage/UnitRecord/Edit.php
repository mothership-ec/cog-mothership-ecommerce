<?php

namespace Message\Mothership\Ecommerce\ProductPage\UnitRecord;

use Message\Cog\DB;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit;

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
		$this->_transaction = $transaction;
		$this->_transOverride = true;
	}

	public function save(Page $page, Product $product, $units, $deleteExisting = true)
	{
		if (!is_array($units) && !$units instanceof Unit\Collection) {
			throw new \InvalidArgumentException('Units must be either an array or a Unit\\Collection instance');
		}

		if ($deleteExisting) {
			$this->_transaction->add("
				DELETE FROM
					product_page_unit_record
				WHERE
					page_id = :pageID?i
			", [
				'pageID' => $page->id,
			]);
		}

		if (count($units) > 0) {
			$inserts = [];
			$params = [
				'pageID' => $page->id,
				'productID' => $product->id,
			];

			foreach ($units as $unit) {
				$inserts[] = '(' . PHP_EOL .
						':pageID?i,' . PHP_EOL .
						':productID?i,' . PHP_EOL .
						':unitID' . $unit->id . '?i' . PHP_EOL .
					')';
				$params['unitID' . $unit->id] = $unit->id;
			}

			$inserts = implode(',' . PHP_EOL, $inserts);

			$this->_transaction->add("
			REPLACE INTO
				product_page_unit_record
				(
					page_id,
					product_id,
					unit_id
				)
			VALUES
		" . $inserts, $params);
		}

		$this->_commitTransaction();
	}

	private function _commitTransaction()
	{
		if (false === $this->_transOverride) {
			$this->_transaction->commit();
		}
	}
}