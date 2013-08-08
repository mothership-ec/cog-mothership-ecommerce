<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Cog\Controller\Controller;
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

	protected $_statusCodes = array(
		'printed'       => OrderItemStatuses::PRINTED,
		'picked'        => OrderItemStatuses::PICKED,
		'packed'        => OrderItemStatuses::PACKED,
		'postaged'      => OrderItemStatuses::POSTAGED,
		'dispatched'    => OrderItemStatuses::DISPATCHED,
	);

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
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::AWAITING_DISPATCH);
		$heading = $this->trans('ms.ecom.fulfillment.new', array('quantity' => count($orders)));
		$form = $this->get('form.orders.checkbox')->build($orders, 'new', 'ms.ecom.fulfillment.process.print.slip');

		return $this->render('::fulfillment:fulfillment:checkbox', array(
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
			OrderItemStatuses::DISPATCHED,
		));

		$heading = $this->trans('ms.ecom.fulfillment.active', array('quantity' => count($orders)));

		return $this->render('::fulfillment:fulfillment:active', array(
			'orders'        => $orders,
			'heading'       => $heading,
			'history'       => $this->_getOrdersHistory($orders),
			'statusCodes'   => $this->_statusCodes,
		));
	}

	public function pickOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PRINTED);
		$heading = $this->trans('ms.ecom.fulfillment.pick', array('quantity' => count($orders)));

		return $this->render('::fulfillment:fulfillment:link', array(
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

		return $this->render('::fulfillment:fulfillment:link', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'action'    => 'Pack',
			'linkRoute' => 'ms.ecom.fulfillment.process.pack'
		));
	}

	public function postOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PACKED);
		$heading = $this->trans('ms.ecom.fulfillment.post', array('quantity' => count($orders)));
		$dispatchTypes = $this->_getDispatches($orders);

		return $this->render('::fulfillment:fulfillment:post', array(
			'dispatchTypes' => $dispatchTypes,
			'heading'       => $heading,
			'action'        => 'Post',
			'linkRoute'     => 'ms.ecom.fulfillment.process.post',
		));
	}

	public function pickupOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::POSTAGED);
		$heading = $this->trans('ms.ecom.fulfillment.pickup', array('quantity' => count($orders)));
		$dispatchTypes = $this->_getDispatches($orders);

		foreach ($dispatchTypes as $name => &$dispatchType) {
			$dispatchType['form'] = $this->get('form.pickup')->build(
				$dispatchType['orders'],
				$name,
				'ms.ecom.fulfillment.process.pickup.action'
			)->getForm()->createView();
		}

		return $this->render('::fulfillment:fulfillment:pickup', array(
			'dispatchTypes' => $dispatchTypes,
			'heading'       => $heading,
			'action'        => 'Pick up'
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

	/**
	 * @todo this is a placeholder until we get the proper dispatch types
	 */
	protected function _getDispatches($orders)
	{
		return array(
			'fedex' => array('orders' => $orders),
			'fedexuk' => array('orders' => $orders)
		);
	}

	protected function _getOrdersHistory($orders)
	{
		$history = array();
		foreach ($orders as $order) {
			$history[$order->id] = $this->_getHistory($order);
		}

		return $history;
	}

	protected function _getHistory($order)
	{
		$history = array(
				'printed'   => array(
					'users' => $this->_getOrderStatusUsers($order, OrderItemStatuses::PRINTED)
				),
				'picked'    => array(
					'users' => $this->_getOrderStatusUsers($order, OrderItemStatuses::PICKED)
				),
				'packed'    => array(
					'users' => $this->_getOrderStatusUsers($order, OrderItemStatuses::PACKED)
				),
				'postaged'  => array(
					'users' => $this->_getOrderStatusUsers($order, OrderItemStatuses::POSTAGED)
				),
				'dispatched'  => array(
					'users' => $this->_getOrderStatusUsers($order, OrderItemStatuses::DISPATCHED)
				),
		);

		return $history;
	}

	public function _getOrderStatusUsers($order, $statusCode)
	{
		$items = $order->items->getIterator();
		$users = array();
		foreach ($items as $item) {
			$history = $this->get('order.item.status.loader')->getHistory($item);
			foreach ($history as $status) {
				if ($status->code != $statusCode) {
					continue;
				}
				$users = $this->_addUserToStatus($users, $item);
			}
		}
		$users = array_unique($users);

		return implode(', ', $users);
	}

	protected function _getUserList($items)
	{
		$users = array();
		foreach ($items as $item) {
			$users = $this->_addUserToStatus($users, $item);
		}

		return implode(', ', $users);
	}


	protected function _addUserToStatus(array $users, $item)
	{
		$history = $this->get('order.item.status.loader')->getHistory($item);
		foreach ($history as $status) {
			$user = $this->_getUser($status->authorship->createdBy());
			$users[] = ($user) ? $user->getInitials() : '';
		}

		return $users;
	}

	protected function _getUser($id)
	{
		return $this->get('user.loader')->getByID($id);
	}

}