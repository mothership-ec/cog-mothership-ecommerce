<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\OrderItemStatuses;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Process
 * @package Message\Mothership\Ecommerce\Controller\Fulfillment
 *
 * Controller for processing orders in Fulfillment
 */
class Process extends Controller
{
	protected $_orderItems;
	protected $_order;

	public function printOrders()
	{

	}

	public function printAction()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(OrderItemStatuses::HOLD);
		$form = $this->get('form.orders.checkbox')->build($orders, 'new');

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$this->_updateOrderStatus($data['choices'], OrderItemStatuses::PRINTED);
			$this->addFlash('success', 'Orders updated successfully');
		}

		return $this->redirectToReferer();
	}

	public function pickOrders($orderID)
	{
		$form  = $this->_getPickForm($orderID);

		$heading = $this->trans('ms.ecom.fulfillment.process.pick', array('order_id' => $orderID));

		return $this->render('::fulfillment:process:select', array(
			'form'      => $form,
			'items'     => $this->_getOrderItems($orderID),
			'heading'   => $heading,
			'action'    => 'Pick'
		));
	}

	public function pickAction($orderID)
	{
		$form = $this->_getPickForm($orderID);

		if ($form->isValid() && $data = $form->getFilteredData()) {

			$status = ($data['packed']) ? OrderItemStatuses::PACKED : OrderItemStatuses::PICKED;
			$this->_updateItemStatus($orderID, $data['choices'], $status);

			$this->addFlash(
				'success',
				$this->trans('ms.ecom.fulfillment.process.success.' . (($data['next'] ? 'pack' : 'pick')))
			);
		}

		return $this->redirectToReferer();
	}

	public function packOrders($orderID)
	{
		$form  = $this->_getPackForm($orderID);

		$heading = $this->trans('ms.ecom.fulfillment.process.pack', array('order_id' => $orderID));

		return $this->render('::fulfillment:process:select', array(
			'form'      => $form,
			'items'     => $this->_getOrderItems($orderID),
			'heading'   => $heading,
			'action'    => 'Pack'
		));
	}

	public function packAction($orderID)
	{
		$form = $this->_getPackForm($orderID);

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$this->_updateItemStatus($orderID, $data['choices'], OrderItemStatuses::PACKED);

			$this->addFlash(
				'success',
				$this->trans('ms.ecom.fulfillment.process.success.pack')
			);
		}

		return $this->redirectToReferer();
	}

	public function postOrders()
	{

	}

	public function postAction()
	{

	}

	public function pickupOrders()
	{

	}

	public function pickupAction()
	{

	}

	protected function _getPickForm($orderID)
	{
		$form = $this->get('form');

		$form->setMethod('post')
			->setAction($this->generateUrl('ms.ecom.fulfillment.process.pick.action', array('orderID' => $orderID)))
			->setName('pick');

		$form->add('choices', 'choice', 'Order items', array(
			'expanded'  => true,
			'multiple'  => true,
			'choices'   => $this->_getOrderFormChoices($orderID),
		));

		$form->add('confirm', 'checkbox', $this->trans('ms.ecom.fulfillment.form.pick.confirm'));

		$form->add('packed', 'checkbox', $this->trans('ms.ecom.fulfillment.form.mark.packed'))
			->val()->optional();

		return $form;
	}

	protected function _getPackForm($orderID)
	{
		$form = $this->get('form');

		$form->setMethod('post')
			->setAction($this->generateUrl('ms.ecom.fulfillment.process.pack.action', array('orderID' => $orderID)))
			->setName('pack');

		$choices = $this->_getOrderFormChoices($orderID);

		$form->add('choices', 'choice', 'Order items', array(
			'expanded'  => true,
			'multiple'  => true,
			'choices'   => $choices
		));

		if (count($choices) > 1) {
			$form->add('split', 'checkbox', $this->trans('ms.ecom.fulfillment.form.pack.split'))
				->val()->optional();
		}

		return $form;

	}

	/**
	 * Load Order object unless already defined
	 *
	 * @param $orderID
	 *
	 * @return \Message\Mothership\Commerce\Order\Order
	 */
	protected function _getOrder($orderID)
	{
		if (!$this->_order || $this->_order->id != $orderID) {
			$this->_order = $this->get('order.loader')->getByID($orderID);
		}

		return $this->get('order.loader')->getByID($orderID);
	}

	/**
	 * Load item entities from order
	 *
	 * @param $orderID
	 *
	 * @return array
	 */
	protected function _getOrderItems($orderID)
	{
		$items = array();
		foreach ($this->_getOrder($orderID)->getItems()->all() as $item) {
			$items[] = $item;
		}

		return $items;

	}

	/**
	 * Generate array for form
	 *
	 * @param $orderID
	 *
	 * @return array
	 */
	protected function _getOrderFormChoices($orderID)
	{
		$items      = $this->_getOrderItems($orderID);
		$choices    = array();

		foreach ($items as $item) {
			$choices[$item->id] = $item->id;
		}

		return $choices;
	}

	protected function _updateOrderStatus(array $orderIDs, $status)
	{
		foreach ($orderIDs as $orderID) {
			$orderItems = $this->_getOrderItems($orderID);
			$this->get('order.item.edit')->updateStatus($orderItems, $status);
		}

		return $this;
	}

	/**
	 * Update item statuses for an order
	 *
	 * @param $orderID
	 * @param $itemIDs
	 * @param $status
	 *
	 * @return $this
	 */
	protected function _updateItemStatus($orderID, array $itemIDs, $status)
	{
		$orderItems = $this->_getOrderItems($orderID);
		$this->get('order.item.edit')->updateStatus($orderItems, $status);

		return $this;
	}
}