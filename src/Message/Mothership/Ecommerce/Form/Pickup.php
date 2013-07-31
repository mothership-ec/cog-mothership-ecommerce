<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Pickup extends OrdersAbstract
{
	protected $_orders;

	public function build($orders, $name, $action = null)
	{
		$this->_setup($name, $action);

		$this->add('choices', 'choice', $name, array(
			'expanded'      => true,
			'multiple'      => true,
			'choices'       => $this->_getOrderChoices($orders),
		))->val()->error($this->_container['translator']->trans('ms.ecom.fulfillment.form.error.choice.order'));

		$this->add('all', 'checkbox', 'all')->val()->optional();

		return $this;
	}
}