<?php

namespace Message\Mothership\Ecommerce\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class ObjectCheckbox extends AbstractType
{
	public function getName()
	{
		return 'ecom_object';
	}
}