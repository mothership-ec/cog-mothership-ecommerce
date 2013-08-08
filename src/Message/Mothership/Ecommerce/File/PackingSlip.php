<?php

namespace Message\Mothership\Ecommerce\File;

use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

class PackingSlip implements ContainerAwareInterface
{
	protected $_container;
	protected $_printID;
	protected $_pages = array();
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
		$this->_fileDestination = array_pop($this->_getDirs());
		$this->_container['filesystem']->mkdir($this->_getDirs());

		$this->_pages['manifest'] = $this->_getHtml('::fulfillment:picking:orderList', array(
			'orders'    => $orders,
		));

		foreach ($orders as $order) {
			$this->_pages[$order->id] = $this->_getHtml('::fulfillment:picking:itemList', array(
				'order' => $order,
			));
		}

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
		$dir = array_pop($this->_getDirs(false));

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
}