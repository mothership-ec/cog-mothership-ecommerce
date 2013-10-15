<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Dispatch\Dispatch;

use Message\Mothership\Ecommerce\OrderItemStatuses;

use Message\Cog\Controller\Controller;
use Message\Cog\HTTP\Response;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Process
 * @package Message\Mothership\Ecommerce\Controller\Fulfillment
 *
 * Controller for processing orders in Fulfillment
 *
 * @todo this controller is getting way too heavy, some of this should be moved into other classes maybe
 */
class Process extends Controller
{
	protected $_orderItems;
	protected $_order;

	/**
	 * Set submitted order status to printed
	 *
	 * @return \Message\Cog\HTTP\RedirectResponse
	 */
	public function printAction()
	{
		$loader = $this->get('order.loader');
		$orders = $loader->getByCurrentItemStatus(OrderItemStatuses::AWAITING_DISPATCH);
		$form = $this->_getHiddenOrdersForm($orders);

		if ($form->isValid() && $data = $form->getFilteredData()) {
			foreach ($data as $orderID) {
				$this->_updateItemStatus($orderID, OrderItemStatuses::PRINTED);
			}

			return $this->redirect($this->generateUrl('ms.ecom.fulfillment.active'));
		}

		return $this->redirect($this->generateUrl('ms.ecom.fulfillment.new'));
	}

	/**
	 * Change order status and save packing slip to file
	 *
	 * @return \Message\Cog\HTTP\RedirectResponse|\Message\Cog\HTTP\Response
	 */
	public function printSlip()
	{
		$loader = $this->get('order.loader');
		$orders = $loader->getByCurrentItemStatus(OrderItemStatuses::AWAITING_DISPATCH);
		$form = $this->get('form.orders.checkbox')->build($orders, 'new');

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$printOrders = array();
			foreach ($data['choices'] as $orderID) {
				$printOrders[] = $loader->getByID($orderID);
				$this->_updateItemStatus($orderID, OrderItemStatuses::PRINTED);
			}

			$this->_saveToFile($printOrders);

			$render = $this->render('::fulfillment:picking:print', array(
				'orders'    => $printOrders,
			));

			return $render;
		}

		return $this->redirectToReferer();
	}

	/**
	 * Display form for picking orders
	 *
	 * @param $orderID
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function pickOrders($orderID)
	{
		$form  = $this->_getPickForm($orderID, OrderItemStatuses::PRINTED);
		$order = $this->_getOrder($orderID);
		$packingSlips   = $this->_getFileIDs($order, 'packing-slip');
		$deliveryNotes  = $this->_getFileIDs($order, 'delivery-note');

		$heading = $this->trans('ms.ecom.fulfillment.process.pick', array('order_id' => $orderID));

		return $this->render('::fulfillment:process:select', array(
			'form'          => $form,
			'items'         => $this->_getOrderItems($orderID, OrderItemStatuses::PRINTED),
			'heading'       => $heading,
			'packingSlips'  => $packingSlips,
			'deliveryNotes' => $deliveryNotes,
			'action'        => 'Pick'
		));
	}

	/**
	 * Set order status to picked, or packed if the option is set
	 *
	 * @param $orderID
	 *
	 * @return \Message\Cog\HTTP\RedirectResponse
	 */
	public function pickAction($orderID)
	{
		$form = $this->_getPickForm($orderID, OrderItemStatuses::PRINTED);
		$items = $this->_getOrderItems($orderID, OrderItemStatuses::PRINTED);

		if ($form->isValid() && $data = $form->getFilteredData()) {

			$this->_updateItemStatus($orderID, OrderItemStatuses::PICKED, $data['choices']);

			if (count($data['choices']) < count($items)) {
				$this->_saveNewPackingSlips($orderID, $data['choices']);
			}

			if ($data['packed']) {
				$this->_packActionOrder($orderID, $data['choices']);

				$this->addFlash(
					'success',
					$this->trans('ms.ecom.fulfillment.process.success.pack')
				);

				return $this->redirect($this->generateUrl('ms.ecom.fulfillment.pack'));
			}

			$this->addFlash(
				'success',
				$this->trans('ms.ecom.fulfillment.process.success.' . ((!empty($data['next']) ? 'pack' : 'pick')))
			);

			return $this->redirect($this->generateUrl('ms.ecom.fulfillment.pick'));
		}
		return $this->redirectToReferer();
	}

	/**
	 * Display form for packing orders
	 *
	 * @param $orderID
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function packOrders($orderID)
	{
		$form  = $this->_getPackForm($orderID, OrderItemStatuses::PICKED);
		$order = $this->get('order.loader')->getByID($orderID);
		$packingSlips   = $this->_getFileIDs($order, 'packing-slip');
		$deliveryNotes  = $this->_getFileIDs($order, 'delivery-note');

		$heading = $this->trans('ms.ecom.fulfillment.process.pack', array('order_id' => $orderID));

		return $this->render('::fulfillment:process:select', array(
			'form'          => $form,
			'items'         => $this->_getOrderItems($orderID, OrderItemStatuses::PICKED),
			'heading'       => $heading,
			'packingSlips'  => $packingSlips,
			'deliveryNotes' => $deliveryNotes,
			'action'        => 'Pack'
		));
	}

	/**
	 * Marks the selected items as packed and creates a dispatch for them.
	 *
	 * @param $orderID
	 *
	 * @return \Message\Cog\HTTP\RedirectResponse
	 */
	public function packAction($orderID, $data = null)
	{
		$form = $this->_getPackForm($orderID, OrderItemStatuses::PICKED);

		if (null !== $data or ($form->isValid() && $data = $form->getFilteredData())) {

			$this->_packActionOrder($orderID, $data['choices']);

			$this->addFlash(
				'success',
				$this->trans('ms.ecom.fulfillment.process.success.pack')
			);

			return $this->redirect($this->generateUrl('ms.ecom.fulfillment.pack'));
		}

		return $this->redirectToReferer();
	}

	/**
	 * Pack an order.
	 *
	 * @param  int    $orderID Order to pack
	 * @param  array  $choices Items to pack
	 * @return void
	 */
	protected function _packActionOrder($orderID, array $choices)
	{
		$this->_updateItemStatus($orderID, OrderItemStatuses::PACKED, $choices);

		$order    = $this->_getOrder($orderID);
		$dispatch = new Dispatch;
		$dispatch->order  = $order;
		$dispatch->method = $this->get('order.dispatch.method.selector')->getMethod($order);

		foreach ($choices as $itemID) {
			$dispatch->items->append($order->items->get($itemID));
		}

		$order->dispatches->append($dispatch);

		$this->get('order.dispatch.create')->create($dispatch);
	}

	public function postOrders($orderID, $dispatchID)
	{
		$dispatch = $this->get('order.dispatch.loader')->getByID($dispatchID);
		$packingSlips   = $this->_getFileIDs($dispatch->order, 'packing-slip');
		$deliveryNotes  = $this->_getFileIDs($dispatch->order, 'delivery-note');

		if (!$dispatchID) {
			throw $this->getNotFoundException(sprintf('Dispatch #%s does not exist', $dispatchID));
		}

		if ($orderID != $dispatch->order->id) {
			throw $this->getNotFoundException(sprintf('Dispatch #%s does not belong to Order #%s', $dispatchID, $orderID));
		}

		$address = $dispatch->order->addresses->getByType('delivery');

		$amendAddressUrl = $this->generateUrl('ms.ecom.fulfillment.process.address', array(
			'orderID'    => $orderID,
			'dispatchID' => $dispatchID,
			'addressID'  => $address->id
		));

		return $this->render('::fulfillment:process:post', array(
			'dispatch'        => $dispatch,
			'deliveryAddress' => $address,
			'form'            => $this->_getPostForm($orderID, $dispatchID),
			'packingSlips'    => $packingSlips,
			'deliveryNotes'   => $deliveryNotes,
			'action'          => 'Post'
		));
	}

	public function amendAddress($orderID, $dispatchID, $addressID)
	{

	}

	/**
	 * Form action for manually postaging a dispatch.
	 *
	 * @param  int $orderID    The order ID
	 * @param  int $dispatchID The dispatch ID
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function postAction($orderID, $dispatchID)
	{
		$dispatch = $this->get('order.dispatch.loader')->getByID($dispatchID);

		if (!$dispatchID) {
			throw $this->getNotFoundException(sprintf('Dispatch #%s does not exist', $dispatchID));
		}

		if ($orderID != $dispatch->order->id) {
			throw $this->getNotFoundException(sprintf('Dispatch #%s does not belong to Order #%s', $dispatchID, $orderID));
		}

		$form = $this->_getPostForm($orderID, $dispatchID);

		if ($form->isValid() && $data = $form->getFilteredData()) {
			// Get new database transaction
			$trans = $this->get('db.transaction');

			// Postage the dispatch using the transaction
			$dispatchEdit = $this->get('order.dispatch.edit');
			$dispatchEdit->setTransaction($trans);
			$dispatchEdit->postage($dispatch, $data['deliveryID']);

			// Update status of items in the dispatch to "postaged"
			$itemEdit = $this->get('order.item.edit');
			$itemEdit->setTransaction($trans);
			$itemEdit->updateStatus($dispatch->items, OrderItemStatuses::POSTAGED);

			// Run the transaction, and add success feedback if it worked
			if ($trans->commit()) {
				$this->addFlash('success', $this->trans('ms.ecom.fulfillment.process.success.post'));

				return $this->redirect($this->generateUrl('ms.ecom.fulfillment.post'));
			}

			$this->addFlash('error', $this->trans('An error occured while postaging this dispatch.'));
		}

		return $this->redirectToReferer();
	}

	public function postAutomatically($orderID, $dispatchID)
	{
		$dispatch = $this->get('order.dispatch.loader')->getByID($dispatchID);

		if (!$dispatchID) {
			throw $this->getNotFoundException(sprintf('Dispatch #%s does not exist', $dispatchID));
		}

		if ($orderID != $dispatch->order->id) {
			throw $this->getNotFoundException(sprintf('Dispatch #%s does not belong to Order #%s', $dispatchID, $orderID));
		}

		$event = $this->get('event.dispatcher')->dispatch(
			Order\Events::DISPATCH_POSTAGE_AUTO,
			new Order\Entity\Dispatch\PostageAutomaticallyEvent($dispatch)
		);

		if (!$event->getCode()) {
			throw new \LogicException(sprintf(
				'Automatic postage for order #%s, dispatch #%s failed: no cost was set',
				$orderID,
				$dispatchID
			));
		}

		$trans     = $this->get('db.transaction');
		$labelData = null;
		$docCreate = $this->get('order.document.create');
		$docCreate->setTransaction($trans);

		foreach ($event->getDocuments() as $document) {
			$document->order    = $dispatch->order;
			$document->dispatch = $dispatch;

			$docCreate->create($document);

			// Save a .txt dispatch label to send in the response (for thermal printer)
			if ('dispatch-label' === $document->type && 'txt' === $document->file->getExtension()) {
				$labelData = file_get_contents($document->file->getRealPath());
			}
		}

		$edit = $this->get('order.dispatch.edit');

		$edit->setTransaction($trans);
		$edit->postage($dispatch, $event->getCode(), $event->getCost());

		$itemEdit = $this->get('order.item.edit');
		$itemEdit->setTransaction($trans);
		$itemEdit->updateStatus($dispatch->items, OrderItemStatuses::POSTAGED);

		if ($trans->commit()) {
			$this->addFlash('success', sprintf('Dispatch #%s on order #%s postaged successfully', $dispatchID, $orderID));
		}
		else {
			$this->addFlash('error', 'Automatic postage was successful, but an error occured whilst updating the dispatch. Please try again.');
		}

		$flashesHtml = $this->forward(
			'Message:Mothership:ControlPanel::Controller:Component#flashes',
			array('_format' => 'html'),
			array(),
			false
		)->getContent();

		$response = new Response(json_encode(array(
			'flashes'      => $flashesHtml,
			'code'         => $dispatch->code,
			'labelData'    => $labelData,
			'redirect'     => $this->generateUrl('ms.ecom.fulfillment.post'),
		)));

		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function pickupAction()
	{
		$methods    = $this->get('order.dispatch.methods');
		$trans      = $this->get('db.transaction');
		$dispatches = array();
		$forms      = array();
		$numUpdated = 0;

		$dispatchEdit = $this->get('order.dispatch.edit');
		$itemEdit     = $this->get('order.item.edit');

		$dispatchEdit->setTransaction($trans);
		$itemEdit->setTransaction($trans);

		foreach ($methods as $method) {
			$dispatches[$method->getName()] = $this->get('order.dispatch.loader')->getPostagedUnshipped($method);
			$form = $this->get('form.pickup')->build(
				$dispatches[$method->getName()],
				$method->getName(),
				'ms.ecom.fulfillment.process.pickup.action'
			);

		 	// @todo obviously we can't leave this unvalidated!! work out why the form is rejecting the data!!
//			if ($form->isPost() && $form->isValid() && $data = $form->getFilteredData()) {
			if ($form->isPost() && $data = $form->getPost()) {
				foreach ($data['choices'] as $dispatchID) {
					$dispatch = $this->get('order.dispatch.loader')->getByID((int) $dispatchID);

					// Ship the dispatch using the transaction
					$dispatchEdit->ship($dispatch);

					// Update status of items in the dispatch to "dispatched"
					$itemEdit->updateStatus($dispatch->items, OrderItemStatuses::DISPATCHED);

					$numUpdated++;
				}
			}
		}

		if ($trans->commit()) {
			$this->addFlash('success', $this->get('translator')->transChoice('ms.ecom.fulfillment.process.success.pick-up', $numUpdated, array(
				'%quantity%' => $numUpdated,
			)));
		}
		else {
			$this->addFlash('error', 'Could not mark packages as picked up');
		}

		return $this->redirectToReferer();
	}

	protected function _getPostForm($orderID, $dispatchID)
	{
		$form = $this->get('form');

		$form->setMethod('post')
			->setAction($this->generateUrl('ms.ecom.fulfillment.process.post.action', array(
				'orderID'    => $orderID,
				'dispatchID' => $dispatchID,
			)))
			->setName('post');

		$form->add('deliveryID', 'text', 'Tracking code');

		return $form;
	}

	protected function _getPickForm($orderID, $status = null)
	{
		$form = $this->get('form');

		$form->setMethod('post')
			->setAction($this->generateUrl('ms.ecom.fulfillment.process.pick.action', array('orderID' => $orderID)))
			->setName('pick');

		$form->add('choices', 'choice', 'Order items', array(
			'expanded'  => true,
			'multiple'  => true,
			'choices'   => $this->_getOrderFormChoices($orderID, $status),
		));

		$form->add('confirm', 'checkbox', $this->trans('ms.ecom.fulfillment.form.pick.confirm'));

		$form->add('packed', 'checkbox', $this->trans('ms.ecom.fulfillment.form.mark.packed'))
			->val()->optional();

		return $form;
	}

	protected function _getPackForm($orderID, $status = null)
	{
		$form = $this->get('form');

		$form->setMethod('post')
			->setAction($this->generateUrl('ms.ecom.fulfillment.process.pack.action', array('orderID' => $orderID)))
			->setName('pack');

		$choices = $this->_getOrderFormChoices($orderID, $status);

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
	 * @param $status
	 *
	 * @return array
	 */
	protected function _getOrderItems($orderID, $status = null)
	{
		$order = $this->_getOrder($orderID);
		$items = ($status) ? $order->items->getByCurrentStatusCode($status) : $order->items->all();

		return $items;

	}

	/**
	 * Generate array for form
	 *
	 * @param $orderID
	 *
	 * @return array
	 */
	protected function _getOrderFormChoices($orderID, $status = null)
	{
		$items      = $this->_getOrderItems($orderID, $status);
		$choices    = array();

		foreach ($items as $item) {
			$choices[$item->id] = $item->id;
		}

		return $choices;
	}

	protected function _updateOrderStatus($orderIDs, $status)
	{
		if (!is_array($orderIDs)) {
			$orderIDs = (array) $orderIDs;
		}

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
	protected function _updateItemStatus($orderID, $status, $itemIDs = null)
	{
		// Kinda naff, but I wanted to be able to give data direct from the form, which may include null
		// values
		if ($orderID === null) {
			return false;
		}

		if ($itemIDs) {
			$orderItems = $this->_getItemsFromIDs($orderID, $itemIDs);
		}
		else {
			$orderItems = $this->_getOrderItems($orderID);
		}

		$this->get('order.item.edit')->updateStatus($orderItems, $status);

		return $this;
	}

	protected function _getItemsFromIDs($orderID, array $itemIDs)
	{
		$order = $this->_getOrder($orderID);
		$items = array();

		foreach ($itemIDs as $id) {
			$items[] = $order->items->get($id);
		}

		return $items;
	}

	protected function _processPickedUpForm($form)
	{
		/**
		 * @todo obviously we can't leave this unvalidated!! work out why the form is rejecting the data!!
		 */
//		if ($form->isPost() && $form->isValid() && $data = $form->getFilteredData()) {
		if ($form->isPost() && $data = $form->getPost()) {
			foreach ($data['choices'] as $orderID) {
				$this->_updateOrderStatus($orderID, OrderItemStatuses::DISPATCHED);
			}

			return true;
		}
		return false;
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

	protected function _getHiddenOrdersForm($orders, array $orderIDs = array(), $action = "#")
	{
		$defaults = array();

		foreach ($orderIDs as $orderID) {
			$defaults['order' . $orderID] = $orderID;
		}

		$form = $this->get('form');
		$form->setMethod('post')
			->setAction($action)
			->setDefaultValues($defaults);

		foreach ($orders as $order) {
			$form->add('order' . $order->id, 'hidden')->val()->optional();
		}

		$form->setDefaultValues($defaults);

		return $form;
	}

	protected function _getFileIDs(Order\Order $order, $type)
	{
		$files = $this->get('order.document.loader')->getByOrder($order);
		$ids = array();

		foreach ($files as $file) {
			if ($file->type == $type) {
				$ids[$file->id] = $order->id;
			}
		}

		return $ids;
	}

	protected function _saveToFile(array $orders)
	{
		return $this->get('file.packing_slip')->save($orders);
	}

	protected function _saveNewPackingSlips($orderID, array $choices)
	{
		$this->addFlash('info', $this->trans('ms.ecom.fulfillment.file.packing.new'));

		return $this->get('file.packing_slip')->saveItemLists($orderID, $choices);
	}
}