<?php

namespace Message\Mothership\Ecommerce\ProductPage\UploadRecord;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;

class Builder
{
	/**
	 * @param Page $page
	 * @param Product $product
	 * @param Unit $unit
	 *
	 * @return UploadRecord
	 */
	public function build(Page $page, Product $product, Unit $unit = null)
	{
		$record = $this->_getNewRecordInstance()
			->setPageID($page->id)
			->setProductID($product->id)
		;

		if ($unit) {
			$record->setUnitID($unit->id);
		}

		return $record;
	}

	private function _getNewRecordInstance()
	{
		return new UploadRecord;
	}
}