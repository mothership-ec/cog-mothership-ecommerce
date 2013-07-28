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
		$form = $this->_getItemActionForm($orderID, 'pick');

		if ($form->isValid() && $data = $form->getFilteredData()) {
			foreach ($data['choices'] as $itemID) {
				$item = $this->_getOrder($orderID)->getItems()->get($itemID);
				$item->updateStatus(($data['next']) ? OrderItemStatuses::PACKED : OrderItemStatuses::PICKED);
			}
			$this->addFlash(
				'success',
				$this->trans('ms.ecom.fulfillment.process.success.' . (($data['next'] ? 'pack' : 'pick')))
			);
		}

		return $this->redirectToReferer();
	}

	public function packOrders()
	{

	}

	public function packAction()
	{

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
		$form->add('next', 'checkbox', $this->trans($options['next']))
			->val()->optional();

		return $form;
	}

	protected function _sanitiseOptions($options, $orderID)
	{
		$defaults = array(
			'action'    => '#',
			'confirm'   => 'Confirm',
			'next'      => 'Next'
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
}