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
		$heading = $this->trans('ms.epos.sop.process.new', array('quantity' => count($orders)));

		return $this->render('::process/checkbox', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function activeOrders()
	{
		$orders = $this->_loader->getOrders(/** leave blank to receive all orders */);
		$heading = $this->trans('ms.epos.sop.process.active', array('quantity' => count($orders)));

		return $this->render('::process/display', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function pickOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for pickable orders */);
		$heading = $this->trans('ms.epos.sop.process.pick', array('quantity' => count($orders)));

		return $this->render('::process/link', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function packOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for packable orders */);
		$heading = $this->trans('ms.epos.sop.process.pack', array('quantity' => count($orders)));

		return $this->render('::process/link', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function postOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for postable orders */);
		$heading = $this->trans('ms.epos.sop.process.post', array('quantity' => count($orders)));

		return $this->render('::process/dispatch', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function pickupOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for picked up orders */);
		$heading = $this->trans('ms.epos.sop.process.pickup', array('quantity' => count($orders)));

		return $this->render('::process/dispatch', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	protected function _getCheckboxForm($orders, $name, $action)
	{
		$form = $this->get('form');
		$form->setMethod('post')
			->setaction($action)
			->setName($name);

		$form->add('orders', 'choice', $name, array(
			'expanded'      => true,
			'multiple'      => true,
			'choices'       => $orders,
		));

	}
}