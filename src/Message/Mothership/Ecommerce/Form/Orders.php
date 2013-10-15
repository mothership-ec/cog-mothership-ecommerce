<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Orders extends OrdersAbstract
{
	protected $_orders;

	/**
	 * Build form
	 *
	 * @param $orders
	 * @param $name
	 * @param null $action
	 * @return Orders           Returns $this for chainability
	 */
	public function build($orders, $name, $action = null)
	{
		$this->_setup($name, $action);

		$this
			->add('choices', 'choice', $name, array(
				'expanded'      => true,
				'multiple'      => true,
				'choices'       => $this->_getOrderChoices($orders),
			))
			->val()
			->requiredError($this->_container['translator']->trans('ms.ecom.fulfillment.form.error.choice.order'));

		return $this;
	}

}