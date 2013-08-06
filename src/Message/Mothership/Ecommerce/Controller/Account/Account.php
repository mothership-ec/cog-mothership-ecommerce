<?php

namespace Message\Mothership\Ecommerce\Controller\Account;

use Message\Cog\Controller\Controller;

/**
 * Class Account
 *
 * Controller for processing orders in Fulfillment
 */
class Account extends Controller
{
	public function index()
	{
		de($this->get('user.current'));

		return $this->render('Message:Mothership:Ecommerce::Account:account', array(
			'hello'   => 'hello',
		));
	}

}