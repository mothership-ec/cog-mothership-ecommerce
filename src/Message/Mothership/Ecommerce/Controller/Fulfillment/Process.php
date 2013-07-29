<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\OrderItemStatuses;

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

	}

	public function pickOrders($orderID)
	{
		$form  = $this->_getItemActionForm($orderID, 'pick', array(
			'action' => 'ms.ecom.fulfillment.process.pick.action',
			'confirm' => 'ms.ecom.fulfillment.form.confirm.pick',
			'next'  => 'ms.ecom.fulfillment.form.mark.packed',
		));

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
		$form = $this->_getItemActionForm($orderID, 'pick', array('next' => true));

		if ($form->isValid() && $data = $form->getFilteredData()) {

			$status = ($data['next']) ? OrderItemStatuses::PACKED : OrderItemStatuses::PICKED;
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
		$form  = $this->_getItemActionForm($orderID, 'pack', array(
			'action' => 'ms.ecom.fulfillment.process.pack.action',
			'confirm' => 'ms.ecom.fulfillment.form.confirm.pack',
		));

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
		$form = $this->_getItemActionForm($orderID, 'pack');

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$this->_updateItemStatus($orderID, $data['choices'], OrderItemStatuses::PACKED);

			$this->addFlash(
				'success',
				$this->trans('ms.ecom.fulfillment.process.success.' . (($data['next'] ? 'pack' : 'pick')))
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

	protected function _getItemActionForm($orderID, $name, $options = array())
	{
		$form = $this->get('form');
		$options = $this->_sanitiseOptions($options, $orderID);

		$form->setMethod('post')
			->setAction($options['action'])
			->setName($name);

		$form->add('choices', 'choice', 'Order items', array(
			'expanded'  => true,
			'multiple'  => true,
			'choices'   => $this->_getOrderFormChoices($orderID),
		))->val()->error($this->trans('ms.ecom.fulfillment.form.error.choice.item'));

		$form->add('confirm', 'checkbox', $this->trans($options['confirm']));

		if (array_key_exists('next', $options)) {
			$form->add('next', 'checkbox', $this->trans($options['next']))
				->val()->optional();
		}

		return $form;
	}

	protected function _sanitiseOptions($options, $orderID)
	{
		$defaults = array(
			'action'    => '#',
			'confirm'   => 'Confirm',
		);

		if (array_key_exists('action', $options)) {
			$options['action'] = $this->generateUrl($options['action'], array('orderID' => $orderID));
		}

		return array_merge($defaults, $options);
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
			$items[$item->id] = $item;
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

	/**
	 * Update item statuses for an order
	 *
	 * @param $orderID
	 * @param $itemIDs
	 * @param $status
	 *
	 * @return $this
	 */
	protected function _updateItemStatus($orderID, $itemIDs, $status)
	{
		$orderItems = $this->_getOrderItems($orderID);
		$itemsToUpdate = array();
		foreach ($itemIDs as $itemID) {
			$itemsToUpdate[] = $orderItems[$itemID];
		}

		$this->get('order.item.edit')->updateStatus($itemsToUpdate, $status);

		return $this;
	}
}