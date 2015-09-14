<?php

namespace Message\Mothership\Ecommerce\ProductPage\UnitRecord;

use Message\Cog\DB;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit;

/**
 * Class Edit
 * @package Message\Mothership\Ecommerce\ProductPage\UnitRecord
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for saving unit and product IDs for a product page for quick access in database queries
 */
class Edit implements DB\TransactionalInterface
{
	/**
	 * @var DB\Transaction
	 */
	private $_transaction;

	/**
	 * @var bool
	 */
	private $_transOverride = false;

	/**
	 * @param DB\Transaction $transaction
	 */
	public function __construct(DB\Transaction $transaction)
	{
		$this->_transaction = $transaction;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_transaction = $transaction;
		$this->_transOverride = true;
	}

	/**
	 * Loop through unit IDs and save them with the page ID and product ID
	 *
	 * @param Page $page                         The product page being edited
	 * @param Product $product                   The product assigned to the page
	 * @param array | Unit\Collection $units     The units that fit the requirements of the page
	 * @param bool $deleteExisting               Determines whether existing rows should be deleted before
	 *                                           save, defaults to true
	 */
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

	/**
	 * Commit database transaction if not overridden
	 */
	private function _commitTransaction()
	{
		if (false === $this->_transOverride) {
			$this->_transaction->commit();
		}
	}
}