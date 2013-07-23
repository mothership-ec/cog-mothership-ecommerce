<?php

namespace Message\Mothership\Ecommerce\Form;

use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Mothership\Ecommerce\Form\Type\ObjectCheckbox;
use Symfony\Component\Form\AbstractExtension;

class EcomExtension extends AbstractExtension implements ContainerAwareInterface
{
	protected $_container;

	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;

		return $this;
	}

	protected function loadTypes()
	{
		return array(
			new ObjectCheckbox,
		);
	}
}