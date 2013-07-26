<?php

namespace Message\Mothership\Ecommerce\Controller\Fulfillment;

use Message\Cog\Controller\Controller;

/**
 * Class Process
 * @package Message\Mothership\Ecommerce\Controller\Fulfillment
 *
 * Controller for processing orders in Fulfillment
 */
class Process extends Controller
{
	public function printOrders()
	{

	}

	public function printAction()
	{

	}

	public function pickOrders($orderID)
	{
		$form  = $this->_getItemActionForm($orderID, 'pick', 'ms.ecom.fulfillment.process.pick.action');

		return $this->render('::fulfillment:process:select', array(

		));
	}

	public function pickAction($orderID)
	{

		return $this->render('::fulfillment:process:select', array(

		));
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

	protected function _getItemActionForm($orderID, $name, $action)
	{

	}
}