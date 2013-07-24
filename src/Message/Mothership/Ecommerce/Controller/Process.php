<?php

namespace Message\Mothership\Ecommerce\Controller;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Order\Order;
use Message\Mothership\Ecommerce\OrderItemStatuses;

class Process extends Controller
{
	/**
	 * @var \Message\Mothership\Commerce\Order\Loader
	 */
	protected $_loader;

	/**
	 * @var \Message\Mothership\Commerce\Order\Status\Collection
	 */
	protected $_itemStatus;

	/**
	 * @var \Message\Mothership\Commerce\Order\Status\Collection
	 */
	protected $_orderStatus;

	/**
	 * @todo should we consider renaming these to be consistent with what the orders are, rather than what they will be?
	 */

	public function __construct()
	{
		$this->_loader = $this->get('order.loader');
		$this->_orderStatus = $this->get('order.statuses');
	}

	public function index()
	{
		return $this->redirectToRoute('ms.ecom.process.active');
	}

	public function newOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::HOLD);
		$heading = $this->trans('ms.ecom.sop.process.new', array('quantity' => count($orders)));
		$form = $this->_getCheckboxForm($orders, 'new', '#');

		return $this->render('::process/checkbox', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'form'      => $form,
			'action'    => 'print',
			'noOrders'  => $this->trans('ms.ecom.sop.process.none'),
		));
	}

	public function activeOrders()
	{
		$orders = $this->_loader->getOrders(/** leave blank to receive all orders */);

		$heading = $this->trans('ms.ecom.sop.process.active', array('quantity' => count($orders)));

		return $this->render('::process/display', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function pickOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for pickable orders */);
		$heading = $this->trans('ms.ecom.sop.process.pick', array('quantity' => count($orders)));

		return $this->render('::process/link', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function packOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for packable orders */);
		$heading = $this->trans('ms.ecom.sop.process.pack', array('quantity' => count($orders)));

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
			->setAction($action)
			->setName($name);

		$form->add('choices', 'choice', $name, array(
			'expanded'      => true,
			'multiple'      => true,
			'choices'       => $this->_getOrderChoices($orders),
		));

		return $form;

	}

	protected function _getOrderChoices($orders)
	{
		$choices = array();
		foreach ($orders as $order) {
			$choices[$order->id] = $order->id;
		}

		return $choices;
	}

	protected function _updateOrderStatuses($orders, $status)
	{
		foreach ($orders as $order) {
			/**
			 * Code to update order status once the classes exist
			 */
		}

		return $this;
	}

	private function _getTestOrders()
	{
		$order1 = new Order();
		$order1->id = 123;
		$order2 = new Order();
		$order2->id = 34534;
		$order3 = new Order();
		$order3->id = 9490823;

		$orders = array(
			$order1,
			$order2,
			$order3,
		);

		return $orders;
	}
}