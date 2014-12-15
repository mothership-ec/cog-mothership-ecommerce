<?php

namespace Message\Mothership\Ecommerce\PageType;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Cog\Field\Factory as FieldFactory;

use Message\Mothership\FileManager\File;
use Symfony\Component\Validator\Constraints;

abstract class AbstractProduct implements PageTypeInterface
{
	public function getName()
	{
		return 'product';
	}

	public function getDisplayName()
	{
		return 'Product';
	}

	public function getDescription()
	{
		return 'A page for selling a product';
	}

	public function allowChildren()
	{
		return false;
	}

	public function setFields(FieldFactory $factory)
	{
		$this->_addProductFields($factory);
		$this->_addShippingField($factory);
		$this->_addGalleryField($factory);
		$this->_addCrossSellField($factory);
	}

	protected function _addProductFields(FieldFactory $factory)
	{
		$factory->addGroup('product', 'Product to sell')
			->add($factory->getField('product', 'product')->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank,
				],
				'label' => 'Product to sell',
			]))
			->add($factory->getField('productoption', 'option', 'Option requirement'))
		;

		$factory->add($factory->getField('richtext', 'details')->setFieldOptions([
			'attr' => [
				'data-help-key' => 'page.product.details.help'
			],
			'label' => 'Details (defaults to product description)',
		]));
	}

	protected function _addShippingField(FieldFactory $factory)
	{
		$factory->add($factory->getField('richtext', 'shipping', 'Shipping & Returns'));
	}

	protected function _addGalleryField(FieldFactory $factory)
	{
		$factory->addGroup('gallery', 'Image Gallery')
			->setRepeatable(true, 0, 15)
			->add($factory->getField('file', 'image', 'Image')
				->setAllowedTypes(File\Type::IMAGE)->setFieldOptions([
					'constraints' => [
						new Constraints\NotBlank,
					]
				]))
		;
	}

	protected function _addCrossSellField(FieldFactory $factory)
	{
		$factory->addGroup('cross_sell', 'Cross Sell Products')
			->setRepeatable(true, 0, 3)
			->add($factory->getField('product', 'product', 'Product to sell'))
			->add($factory->getField('productoption', 'option', 'Option requirement'))
		;
	}
}