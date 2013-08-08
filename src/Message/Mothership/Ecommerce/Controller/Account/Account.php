<?php

namespace Message\Mothership\Ecommerce\Controller\Account;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\Form\UserRegister;

/**
 * Class Account
 *
 * Controller for processing orders in Fulfillment
 */
class Account extends Controller
{
	public function index()
	{
		// Load the user
		$user = $this->get('user.current');
		// Load the user addresses
		$addresses = $this->get('commerce.user.loader')->getByUser($user);
		// Get the most recent
		$address = array_shift($addresses);

		return $this->render('Message:Mothership:Ecommerce::Account:account', array(
			'user'    => $user,
			'address' => $address,
		));
	}

	public function orderListing()
	{
		// Load the logged in user
		$user = $this->get('user.current');
		// return their orders
		$orders = $this->get('order.loader')->getByUser($user);

		return $this->render('Message:Mothership:Commerce::order:all-orders:summary', array(
			'orders' => $orders,
		));
	}

	public function orderDetail($orderID)
	{
		// Load the current order 
		$order = $this->get('order.loader')->getByID($orderID);

		//de($order);

		return $this->render('Message:Mothership:Ecommerce::Account:orderdetails', array(
			'order' => $order,
		));
		
	}

}