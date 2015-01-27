<?php


namespace Message\Mothership\Ecommerce\ProductPage\UploadRecord;

use Message\Cog\ValueObject\Collection as BaseCollection;

class Collection extends BaseCollection
{
	public function __construct(array $records)
	{
		$this->addValidator(function ($record) {
			if (!$record instanceof UploadRecord) {
				throw new \InvalidArgumentException('Record must be an instance of UploadRecord');
			}
		});

		$this->setKey(function (UploadRecord $record) {
			return $record->getPageID();
		});

		parent::__construct($records);
	}
}