<?php

namespace Message\Mothership\Ecommerce\Form\Product;

use Message\Mothership\Ecommerce\ProductPage\UploadRecord\Collection;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;

class ProductPagePublish extends Form\AbstractType
{
	const PUBLISH = 'publish';

	private $_records;

	public function setRecords(Collection $records)
	{
		$this->_records = $records;

		return $this;
	}

	public function getName()
	{
		return 'product_page_publish';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		if (!$this->_records) {
			throw new \LogicException('Records not set!');
		}

		$builder->add(self::PUBLISH, 'choice', [
			'constraints' => [
				new Constraints\NotBlank
			],
			'expanded' => true,
			'multiple' => true,
			'choices'  => $this->_getFormChoices(),
		]);
	}

	private function _getFormChoices()
	{
		$choices = [];

		foreach ($this->_records as $record) {
			$choices[$record->getPageID()] = $record->getPageTitle();
		}

		return $choices;
	}

}