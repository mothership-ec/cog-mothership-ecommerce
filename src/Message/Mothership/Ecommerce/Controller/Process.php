<?php

namespace Message\Mothership\Ecommerce\Controller;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Order\Order;

class Process extends Controller
{
	/**
	 * @var \Message\Mothership\Commerce\Order\Loader
	 */
	protected $_loader;

	/**
	 * @todo should we consider renaming these to be consistent with what the orders are, rather than what they will be?
	 */

	public function __construct()
	{
		$this->_loader = $this->get('order.loader');
	}

	public function index()
	{
		return $this->redirectToRoute('ms.ecom.process.active');
	}

	public function newOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for new orders */);
	}

	public function activeOrders()
	{
		$orders = $this->_loader->getOrders(/** leave blank to receive all orders */);
	}

	public function pickOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for pickable orders */);
	}

	public function packOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for packable orders */);
	}

	public function postOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for postable orders */);
	}

	public function pickupOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for picked up orders */);
	}
}