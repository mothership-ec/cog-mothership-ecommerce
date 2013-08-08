<?php

namespace Message\Mothership\Ecommerce\File;

use Message\Cog\DB\Query;

class Loader
{
	protected $_query;
	protected $_files = array();
	protected $_ids = array();

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function content($orderID, $type = null, $documentID = null)
	{
		$this->_load($orderID, $type, $documentID);

		return $documentID ? $this->_files[$documentID] : $this->_files;
	}

	public function id($orderID, $type = null, $documentID = null)
	{
		$this->_load($orderID, $type, $documentID);

		return $documentID ? $this->_ids[$documentID] : $this->_ids;
	}

	protected function _load($orderID, $type)
	{
		$files = $this->_query->run($this->_getSql($type), array(
			'order_id' => $orderID,
			'type' => $type,
		));

		$this->_setFiles($files);
	}

	protected function _getSql($type)
	{
		return "
			SELECT
				document_id,
				order_id,
				url
			FROM
				order_document
			WHERE
				order_id = :order_id?i" .
			($type ? "
				AND
					type = :type?s": ''
			);
	}

	protected function _setFiles($files)
	{
		foreach ($files as $file) {
			$this->_files[$file->document_id] = file_get_contents($file->url);
			$this->_ids[$file->document_id] = $file->order_id;
		}
	}
}