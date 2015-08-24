<?php

namespace Message\Mothership\Ecommerce\Filter;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Mothership\CMS\Page\Filter\AbstractContentFilter;
use Message\Mothership\Commerce\Product\Unit\Loader as UnitLoader;
use Message\Mothership\CMS\Page\Filter\ContentFilter;
use Message\Cog\DB\QueryBuilderFactory;

/**
 * @author Samuel Trangmar-Keates
 *
 * Sale filter class for the PageLoader. Filters the results to only show pages
 * for products in the sale.
 */
class SaleFilter extends AbstractContentFilter
{
	protected $_productGroup      = 'product';
	protected $_productFieldName  = 'product';
	protected $_optionFieldName   = 'option';

	protected $_unitLoader;
	protected $_queryBuilderFactory;

	/**
	 * {@inheritDocs}
	 * 
	 * @param UnitLoader          $unitLoader          Used to load sale Units
	 * @param QueryBuilderFactory $queryBuilderFactory Required for sub-queries on the filter step
	 */
	public function __construct($name, $displayName, UnitLoader $unitLoader, QueryBuilderFactory $queryBuilderFactory)
	{
		parent::__construct($name, $displayName);

		$this->_unitLoader =  $unitLoader;
		$this->_queryBuilderFactory = $queryBuilderFactory;
	}

	/**
	 * Sets the group to check, defaults to product
	 */
	public function setGroup($group)
	{
		if (!is_string($group)) {
			throw new \InvalidArgumentException('Method `setGroup()` expects argument one to be of type `string`, `' . gettype($group) . '` given');
		}

		$this->_productGroup = $group;

		return $this;
	}

	/**
	 * Sets the name of the field to check for the product
	 *
	 * @deprecated No longer necessary since creation of `product_page_unit_record` table
	 */
	public function setProductField($field)
	{
		if (!is_string($field)) {
			throw new \InvalidArgumentException('Method `setProductField()` expects argument one to be of type `string`, `' . gettype($field) . '` given');
		}

		$this->_productFieldName = $field;

		return $this;
	}

	/**
	 * Sets the name of the field to check for the product option
	 *
	 * @deprecated No longer necessary since creation of `product_page_unit_record` table
	 */
	public function setOptionField($field)
	{
		if (!is_string($field)) {
			throw new \InvalidArgumentException('Method `setOptionField()` expects argument one to be of type `string`, `' . gettype($field) . '` given');
		}

		$this->_optionFieldName = $field;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{
		if (empty($this->_value)) {
			return;
		}

		$units = $this->_unitLoader->getSaleUnits();

		array_walk($units, function (&$unit) {
			$unit = $unit->id;
		});

		$queryBuilder->join('product_page_unit_record', 'product_page_unit_record.page_id = page.page_id')
			->where('product_page_unit_record.unit_id IN (?ji)', [$units])
		;

	}
}
