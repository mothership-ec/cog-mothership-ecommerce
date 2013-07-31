<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class OrdersAbstract extends Handler
{
	/**
	 * Set method, action and name of form
	 *
	 * @param string $name              Form name
	 * @param string | null $action     Form action
	 * @param array $params             Parameters for action
	 *
	 * @return OrdersAbstract           Returns $this for chainability
	 */
	protected function _setup($name, $action = null, array $params = array())
	{
		$action = ($action) ? $this->_generateUrl($action, $params) : '#';

		$this->setMethod('post')
			->setAction($action)
			->setName($name);

		return $this;
	}

	/**
	 * Generate URL from route name
	 *
	 * @param $routeName
	 *
	 * @return mixed
	 */
	protected function _generateUrl($routeName)
	{
		return $this->_container['routing.generator']->generate($routeName, array(), UrlGeneratorInterface::ABSOLUTE_PATH);
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
}