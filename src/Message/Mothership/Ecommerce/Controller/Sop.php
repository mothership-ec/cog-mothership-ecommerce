<?php

namespace Message\Mothership\Ecommerce\Controller;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Order\Order;
use Message\Mothership\Ecommerce\OrderItemStatuses;

/**
 * Class Sop
 * @package Message\Mothership\Ecommerce\Controller
 *
 * Controller for viewing orders in SOP
 */
class Sop extends Controller
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

	public function __construct()
	{
		$this->_loader = $this->get('order.loader');
		$this->_orderStatus = $this->get('order.statuses');
	}

	public function index()
	{
		return $this->redirectToRoute('ms.ecom.sop.active');
	}

	public function newOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::HOLD);
		$heading = $this->trans('ms.ecom.sop.new', array('quantity' => count($orders)));
		$form = $this->_getCheckboxForm($orders, 'new', '#');

		return $this->render('::sop:checkbox', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'form'      => $form,
			'action'    => 'Print',
		));
	}

	public function activeOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(array(
			OrderItemStatuses::PRINTED,
			OrderItemStatuses::PICKED,
			OrderItemStatuses::PACKED,
			OrderItemStatuses::POSTAGED,
		));

		$heading = $this->trans('ms.ecom.sop.active', array('quantity' => count($orders)));

		return $this->render('::sop:active', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function pickOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PRINTED);
		$heading = $this->trans('ms.ecom.sop.pick', array('quantity' => count($orders)));

		return $this->render('::sop:link', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'action'    => 'Pack'
		));
	}

	public function packOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PICKED);
		$heading = $this->trans('ms.ecom.sop.pack', array('quantity' => count($orders)));

		return $this->render('::sop:link', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'action'    => 'Post'
		));
	}

	public function postOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::POSTAGED);
		$heading = $this->trans('ms.epos.sop.post', array('quantity' => count($orders)));

		return $this->render('::sop:dispatch', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function pickupOrders()
	{
		$orders = $this->_loader->getOrders(/** constant for picked up orders */);
		$heading = $this->trans('ms.epos.sop.pickup', array('quantity' => count($orders)));

		return $this->render('::sop:dispatch', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function tabs()
	{
		$tabs = array(
			'New'       => $this->generateUrl('ms.ecom.sop.new'),
			'Active'    => $this->generateUrl('ms.ecom.sop.active'),
			'Pick'      => $this->generateUrl('ms.ecom.sop.pick'),
			'Pack'      => $this->generateUrl('ms.ecom.sop.pack'),
			'Post'      => $this->generateUrl('ms.ecom.sop.pack'),
			'Pick up'   => $this->generateUrl('ms.ecom.sop.pickup'),
		);

		$current = ucfirst(trim(strrchr($this->get('http.request.master')->get('_controller'), '::'), ':'));
		return $this->render('Message:Mothership:Ecommerce::tabs', array(
			'tabs'    => $tabs,
			'current' => $current,
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