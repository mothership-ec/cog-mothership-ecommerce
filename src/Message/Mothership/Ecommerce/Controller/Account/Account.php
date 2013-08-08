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
		return $this->render('Message:Mothership:Ecommerce::Account:account');
	}

}