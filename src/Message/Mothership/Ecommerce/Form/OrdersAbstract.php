<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class OrdersAbstract extends Handler
{
	protected function _setup($name, $action = null)
	{
		$action = ($action) ? $this->_generateUrl($action) : '#';

		$this->setMethod('post')
			->setAction($action)
			->setName($name);
	}

	protected function _generateUrl($routeName)
	{
		return $this->_container['routing.generator']->generate($routeName, array(), UrlGeneratorInterface::ABSOLUTE_PATH);
	}
}