<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\Event;
use Message\Mothership\Ecommerce\OrderItemStatuses;
use Message\Mothership\Commerce\Order\Statuses as OrderStatuses;
use Message\Mothership\Commerce\Order\Order;
use Message\Mothership\Commerce\Order\Entity\Dispatch\Dispatch;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent;

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
		$event = new BuildMenuEvent;

		$this->get('event.dispatcher')->dispatch(
			Event::FULFILLMENT_MENU_BUILD,
			$event
		);

		$event->setClassOnCurrent($this->get('http.request.master'), 'active');

		return $this->render('Message:Mothership:Ecommerce::tabs', array(
			'items' => $event->getItems(),
		));
	}

	public function newOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::AWAITING_DISPATCH);
		$orders = $this->_filterWebOrders($orders);
		$orders = array_values($orders);
		$heading = $this->trans('ms.ecom.fulfillment.new', array('quantity' => count($orders)));
		$form = $this->get('form.orders.checkbox')
			->addOptions(array('attr' => array(
				'target' => '_blank',
			)))
			->build($orders, 'new', 'ms.ecom.fulfillment.process.print.slip');

		return $this->render('Message:Mothership:Ecommerce::fulfillment:fulfillment:checkbox', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'form'      => $form,
			'action'    => 'Print',
		));
	}

	public function activeOrders()
	{
		$ids = $this->get('db.query')->run('
			SELECT
				order_id
			FROM
				order_summary
			WHERE
				status_code IN (?ij) OR
				(status_code = ?i AND updated_at > ? AND updated_at < ?)
		', array(
			array(
				OrderStatuses::AWAITING_DISPATCH,
				OrderStatuses::PROCESSING,
				OrderStatuses::PARTIALLY_DISPATCHED,
			),
			OrderStatuses::DISPATCHED,
			strtotime(date('Y-m-d 00:00:00')),
			strtotime(date('Y-m-d 23:59:59')),
		));

		$orders = $this->get('order.loader')->getByID($ids->flatten());
		$orders = $this->_filterWebOrders($orders);
		$orders = array_values($orders);

		$heading = $this->trans('ms.ecom.fulfillment.active', array('quantity' => count($orders)));

		return $this->render('Message:Mothership:Ecommerce::fulfillment:fulfillment:active', array(
			'orders'        => $orders,
			'heading'       => $heading,
			'history'       => $this->_getOrdersHistory($orders),
			'statusCodes'   => $this->_statusCodes,
		));
	}

	public function pickOrders()
	{
		$orders  = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PRINTED);
		$orders  = $this->_filterWebOrders($orders);
		$orders  = array_values($orders);
		$heading = $this->trans('ms.ecom.fulfillment.pick', array('quantity' => count($orders)));

		return $this->render('Message:Mothership:Ecommerce::fulfillment:fulfillment:link', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'action'    => 'Pick',
			'linkRoute' => 'ms.ecom.fulfillment.process.pick'
		));
	}

	public function packOrders()
	{
		$orders  = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::PICKED);
		$orders  = $this->_filterWebOrders($orders);
		$orders  = array_values($orders);
		$heading = $this->trans('ms.ecom.fulfillment.pack', array('quantity' => count($orders)));

		return $this->render('Message:Mothership:Ecommerce::fulfillment:fulfillment:link', array(
			'orders'    => $orders,
			'heading'   => $heading,
			'action'    => 'Pack',
			'linkRoute' => 'ms.ecom.fulfillment.process.pack'
		));
	}

	public function postOrders()
	{
		$methods    = $this->get('order.dispatch.methods');
		$dispatches = array();

		foreach ($methods as $method) {
			$dispatches[$method->getName()] = $this->get('order.dispatch.loader')->getUnpostaged($method);
			$dispatches[$method->getName()] = $this->_filterWebDispatches($dispatches[$method->getName()]);
			$dispatches[$method->getName()] = array_values($dispatches[$method->getName()]);
		}

		return $this->render('Message:Mothership:Ecommerce::fulfillment:fulfillment:post', array(
			'methods'    => $methods,
			'dispatches' => $dispatches,
			'action'     => 'Post',
			'linkRoute'  => 'ms.ecom.fulfillment.process.post',
		));
	}

	public function pickupOrders()
	{
		$form = $this->get('form.fulfillment.pickup');
		$methods = $this->get('order.dispatch.methods');
		$form = $this->createForm($form, null, [
			'action'  => $this->generateUrl('ms.ecom.fulfillment.process.pickup.action'),
			'methods' => $methods
		]);

		return $this->render('Message:Mothership:Ecommerce::fulfillment:fulfillment:pickup', [
			'form'       => $form,
			'methods'    => $methods,
			'dispatches' => $this->get('order.dispatch.loader')->getPostagedUnshipped(),
			'action'     => 'Pick up'
		]);

		$methods = $this->get('order.dispatch.methods');
		$dispatches  = array();
		$forms       = array();

		foreach ($methods as $method) {
			$dispatches[$method->getName()] = $this->get('order.dispatch.loader')->getPostagedUnshipped($method);
			$dispatches[$method->getName()] = $this->_filterWebDispatches($dispatches[$method->getName()]);
			$dispatches[$method->getName()] = array_values($dispatches[$method->getName()]);
			de($dispatches[$method->getName()]);
			$forms[$method->getName()] = $this->get('form.pickup')->build(
				$dispatches[$method->getName()],
				$method->getName(),
				'ms.ecom.fulfillment.process.pickup.action'
			)->getForm()->createView();
		}

		return $this->render('Message:Mothership:Ecommerce::fulfillment:fulfillment:pickup', array(
			'forms'      => $forms,
			'methods'    => $methods,
			'dispatches' => $dispatches,
			'action'     => 'Pick up'
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
				'printed'    => $this->_getOrderStatusProgress($order, OrderItemStatuses::PRINTED),
				'picked'     => $this->_getOrderStatusProgress($order, OrderItemStatuses::PICKED),
				'packed'     => $this->_getOrderStatusProgress($order, OrderItemStatuses::PACKED),
				'postaged'   => $this->_getOrderStatusProgress($order, OrderItemStatuses::POSTAGED),
				'dispatched' => $this->_getOrderStatusProgress($order, OrderItemStatuses::DISPATCHED),
		);

		return $history;
	}

	public function _getOrderStatusProgress($order, $statusCode)
	{
		$users = array();
		$itemsWithStatus = 0;

		foreach ($order->items as $item) {
			$history = $this->get('order.item.status.loader')->getHistory($item);
			foreach ($history as $status) {
				if ($status->code === $statusCode) {
					$itemsWithStatus++;

					$id = $status->authorship->createdBy();
					if (! isset($users[$id])) {
						if ($user = $this->get('user.loader')->getByID($id)) {
							$users[$id] = $user->getInitials();
						}
					}
				}
			}
		}
		$users = array_unique($users);

		$progress = (float) $itemsWithStatus / (float) count($order->items);

		return array(
			'users'    => implode(', ', $users),
			'progress' => $progress,
		);
	}

	/**
	 * Filter out any orders that do not have a type of 'web'
	 * @todo load only the correct orders in the first place
	 *
	 * @param $orders
	 * @return array
	 */
	protected function _filterWebOrders($orders)
	{
		$webOrders = array();

		foreach ($orders as $key => $order) {
			if ($order instanceof Order && $order->type == 'web') {
				$webOrders[$key] = $order;
			}
		}

		return $webOrders;
	}

	/**
	 * Filter out any dispatches that do not have an order type of 'web'.
	 * @todo load only the correct dispatches in the first place
	 *
	 * @param $dispatches
	 * @return array
	 */
	protected function _filterWebDispatches($dispatches)
	{
		$webDispatches = array();

		foreach ($dispatches as $key => $dispatch) {
			if ($dispatch instanceof Dispatch && $dispatch->order && $dispatch->order->type =='web') {
				$webDispatches[$key] = $dispatch;
			}
		}

		return $webDispatches;
	}

}