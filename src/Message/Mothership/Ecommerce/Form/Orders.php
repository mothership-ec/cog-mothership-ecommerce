<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Orders extends Handler
{
	protected $_orders;

	public function __construct(Container $container)
	{
		parent::__construct($container);
	}

	public function build($orders, $name, $action = null)
	{
		$action = ($action) ? $this->_generateUrl($action) : '#';

		$this->setMethod('post')
			->setAction($action)
			->setName($name);

		$this->add('choices', 'choice', $name, array(
			'expanded'      => true,
			'multiple'      => true,
			'choices'       => $this->_getOrderChoices($orders),
		))->val()->error($this->_container['translator']->trans('ms.ecom.fulfillment.form.error.choice.order'));

		return $this;
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

	protected function _generateUrl($routeName)
	{
		return $this->_container['routing.generator']->generate($routeName, array(), UrlGeneratorInterface::ABSOLUTE_PATH);
	}
}