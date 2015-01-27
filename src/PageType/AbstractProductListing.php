<?php

namespace Message\Mothership\Ecommerce\PageType;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Cog\Field\Factory as FieldFactory;

abstract class AbstractProductListing implements PageTypeInterface
{
	public function getName()
	{
		return 'product_listing';
	}

	public function getDisplayName()
	{
		return 'Product listing';
	}

	public function getDescription()
	{
		return 'A page for listing products';
	}

	public function allowChildren()
	{
		return true;
	}

	public function setFields(FieldFactory $factory)
	{}
}