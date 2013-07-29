<?php

namespace Message\Mothership\Ecommerce\Controller;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Order\Order;
use Message\Mothership\Ecommerce\OrderItemStatuses;

/**
 * Class Fulfillment
 * @package Message\Mothership\Ecommerce\Controller
 *
 * Controller for viewing orders in Fulfillment
 */
class Fulfillment extends Controller
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
		return $this->redirectToRoute('ms.ecom.fulfillment.active');
	}

	public function tabs()
	{
		$tabs = array(
			'New'       => $this->generateUrl('ms.ecom.fulfillment.new'),
			'Active'    => $this->generateUrl('ms.ecom.fulfillment.active'),
			'Pick'      => $this->generateUrl('ms.ecom.fulfillment.pick'),
			'Pack'      => $this->generateUrl('ms.ecom.fulfillment.pack'),
			'Post'      => $this->generateUrl('ms.ecom.fulfillment.post'),
			'Pick up'   => $this->generateUrl('ms.ecom.fulfillment.pickup'),
		);

		$current = ucfirst(trim(strrchr($this->get('http.request.master')->get('_controller'), '::'), ':'));
		return $this->render('Message:Mothership:Ecommerce::tabs', array(
			'tabs'    => $tabs,
			'current' => $current,
		));
	}

	public function newOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::HOLD);
		$heading = $this->trans('ms.ecom.fulfillment.new', array('quantity' => count($orders)));
		$form = $this->_getCheckboxForm($orders, 'new', 'ms.epos.fulfillment.process.print');

		return $this->render('::fulfillment:checkbox', array(
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

		$heading = $this->trans('ms.ecom.fulfillment.active', array('quantity' => count($orders)));

		return $this->render('::fulfillment:active', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function pickOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PRINTED);
		$heading = $this->trans('ms.ecom.fulfillment.pick', array('quantity' => count($orders)));

		return $this->render('::fulfillment:link', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'action'    => 'Pick',
			'linkRoute' => 'ms.ecom.fulfillment.process.pick'
		));
	}

	public function packOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PICKED);
		$heading = $this->trans('ms.ecom.fulfillment.pack', array('quantity' => count($orders)));

		return $this->render('::fulfillment:link', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'action'    => 'Pack',
			'linkRoute' => 'ms.ecom.fulfillment.process.pack'
		));
	}

	public function postOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PACKED);
		$heading = $this->trans('ms.epos.fulfillment.post', array('quantity' => count($orders)));

		return $this->render('::fulfillment:dispatch', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	public function pickupOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PACKED);
		$heading = $this->trans('ms.epos.fulfillment.pickup', array('quantity' => count($orders)));

		return $this->render('::fulfillment:dispatch', array(
			'orders'    => $orders,
			'heading'   => $heading,
		));
	}

	/**
	 * Build form for checkbox lists
	 *
	 * @param $orders
	 * @param $name
	 * @param $action
	 *
	 * @return \Message\Cog\Form\Handler
	 */
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
		))->val()->error($this->trans('ms.ecom.fulfillment.form.error.choice.order'));

		return $form;

	}

	/**
	 * Get array for of orders for form
	 *
	 * @param $orders
	 *
	 * @return array
	 */
	protected function _getOrderChoices($orders)
	{
		$choices = array();
		foreach ($orders as $order) {
			$choices[$order->id] = $order->id;
		}

		return $choices;
	}

}