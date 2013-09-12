<?php

namespace Message\Mothership\Ecommerce\File;

use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Mothership\Commerce\Order\Entity\Document\Document;
use Message\Cog\Filesystem\File;

class PackingSlip implements ContainerAwareInterface
{
	protected $_container;
	protected $_printID;
	protected $_pages = array();
	protected $_orders = array();
	protected $_date;
	protected $_fileDestination;

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	/**
	 * Save picking slips pages as different files
	 *
	 * @param array $orders
	 */
	public function save($orders)
	{
		$this->_date = date('Ymd');
		$dirs = $this->_getDirs();
		$this->_fileDestination = array_pop($dirs);
		$this->_container['filesystem']->mkdir($this->_getDirs());
		$this->_setOrders($orders);

		$this->_pages['manifest'] = $this->_getHtml('::fulfillment:picking:orderList', array(
			'orders'    => $orders,
		));

		foreach ($orders as $order) {
			$this->_pages[$order->id . '_packing-slip'] = $this->_getHtml('::fulfillment:picking:itemList', array(
				'order' => $order,
			));
			$this->_pages[$order->id . '_delivery-note'] = $this->_getHtml('::fulfillment:picking:deliveryNote', array(
				'order' => $order,
			));
		}

		$this->_savePages();
	}

	public function saveItemLists($orderID, $items)
	{
		$dirs = $this->_getDirs();
		$this->_date = date('Ymd');
		$this->_fileDestination = array_pop($dirs);
		$this->_orders[$orderID] = $this->_container['order.loader']->getByID($orderID);

		$items = $this->_getItems($items);
		$this->_pages[$orderID . '_packing-slip'] = $this->_getHtml('::fulfillment:picking:itemList', array(
			'items' => $items
		));

		$this->_savePages();
	}

	/**
	 * @return int
	 */
	public function getPrintID()
	{
		if (!$this->_printID) {
			$this->_setPrintID();
		}

		return $this->_printID;
	}

	/**
	 * Get location of saved files
	 *
	 * @return string
	 */
	public function getRoute()
	{
		return $this->_fileDestination;
	}

	/**
	 * @param $reference
	 * @param $params
	 * @return string
	 */
	protected function _getHtml($reference, $params)
	{
		return $this->_container['response_builder']
			->setRequest($this->_container['request'])
			->render($reference, $params)
			->getContent();
	}

	/**
	 * Loop through pages and save them to files
	 */
	protected function _savePages()
	{
		foreach ($this->_pages as $name => $page) {
			$this->_createFile($name, $page);

			// Can only save files to DB if attached to an order, so the manifest cannot be saved :(
			if (count(explode('_', $name)) >= 2) {
				$this->_saveToDB($name);
			}
		}
	}

	/**
	 * Create file containing contents. Automatically generates ID to use as file name
	 *
	 * @param $contents
	 * @throws \LogicException
	 * @return bool
	 */
	protected function _createFile($name, $contents)
	{
		$contents = (string) $contents;
		$path = $this->_getPath($name);

		if ($this->_container['filesystem']->exists($path)) {
			throw new \LogicException($path . " already exists, when it shouldn't");
		}

		$manager = $this->_container['filesystem.stream_wrapper_manager'];
		$handler = $manager::getHandler('cog');
		$path = $handler->getLocalPath($path);

		$this->_container['filesystem']->dumpFile($path, $contents);

		return true;
	}

	/**
	 * Create full path and extension for filename
	 *
	 * @param $filename
	 *
	 * @return string
	 */
	protected function _getPath($filename)
	{
		return $this->_fileDestination . '/' . $filename . '.html';
	}

	/**
	 * @return PackingSlip      Returns $this for chainability
	 */
	protected function _setPrintID()
	{
		$id = 0;
		$dirs = $this->_getDirs(false);
		$dir = array_pop($dirs);

		while ($this->_container['filesystem']->exists($dir . '/' . $id)) {
			$id++;
		}

		$this->_printID = $id;

		return $this;
	}

	/**
	 * Get directories to make when saving files
	 *
	 * @param bool $withPrint       Include print ID, set to false when setting print id to stop infinite loop
	 *
	 * @return array
	 */
	protected function _getDirs($withPrint = true)
	{
		$dirs = array(
			'cog://data/order',
			'cog://data/order/picking',
			'cog://data/order/picking/' . $this->_date,
		);

		if ($withPrint) {
			$dirs[] = 'cog://data/order/picking/' . $this->_date . '/' . $this->getPrintID();
		}

		return $dirs;
	}

	/**
	 * @param $orders
	 * @return PackingSlip
	 */
	protected function _setOrders($orders)
	{
		foreach ($orders as $order) {
			$this->_orders[$order->id] = $order;
		}

		return $this;
	}

	/**
	 * Save document info to database
	 *
	 * @param $fileName
	 */
	protected function _saveToDB($fileName)
	{
		list($orderID, $fileType) = explode('_', $fileName);

		$document = new Document;
		$document->order = $this->_orders[$orderID];
		$document->type = $fileType;
		$document->file = new File($this->_getPath($fileName));

		$this->_container['order.document.create']->create($document);
	}

	/**
	 * @param array $itemIDs
	 * @return array
	 */
	protected function _getItems(array $itemIDs)
	{
		$items = array();
		foreach ($itemIDs as $itemID) {
			$items[] = $this->_container['order.item.loader']->getByID($itemID);
		}

		return $items;
	}
}