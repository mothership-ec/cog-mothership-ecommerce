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

		$saleUnits = $this->_unitLoader->getSaleUnits();

		// if no units found then just return no pages
		if (empty($saleUnits)) {
			$queryBuilder->where('FALSE');
			return;
		}

		$constraints = [];
		$productIDs = [];

		// Build sale product variables for the query
		foreach ($saleUnits as $unit) {
			$productIDs[$unit->getProduct()->id] = $unit->getProduct()->id; 

			$constraint = [
				'id'     => $unit->getProduct()->id,
				'names'  => [],
				'values' => [],
			];

			foreach($unit->getOptions() as $name => $value) {
				$constraint['names'][]       = $name;
				$constraint['values'][]      = $value;
			}

			$constraints[] = $constraint;
		}

		$queryBuilderFactory = $this->_queryBuilderFactory;

		$subQuery = function() use ($queryBuilderFactory) {
			return $queryBuilderFactory->getQueryBuilder();
		};

		$contentAlias = $this->_getContentAlias();

		// Query to retrieve pages with the correct options
		$pageSubQuery = $subQuery()
			->select($contentAlias . '_product.page_id')
			->from(
				$contentAlias . '_product',
				$subQuery()
					->select('*')
					->from('page_content')
					->where('page_content.field_name = ?s', [$this->_productFieldName])
					->where('page_content.group_name = ?s', [$this->_productGroup])
			)
			->join(
				$contentAlias . '_product_option_name',
					$contentAlias . '_product_option_name.page_id = ' . $contentAlias . '_product.page_id',
				$subQuery()
					->select('*')
					->from('page_content')
					->where('page_content.field_name = ?s', [$this->_optionFieldName])
					->where('page_content.group_name = ?s', [$this->_productGroup])
					->where('page_content.data_name = \'name\'')
			)
			->join(
				$contentAlias . '_product_option_value',
				$contentAlias . '_product_option_value.page_id = ' . $contentAlias . '_product.page_id',
				$subQuery()
					->select('*')
					->from('page_content')
					->where('page_content.field_name = ?s', [$this->_optionFieldName])
					->where('page_content.group_name = ?s', [$this->_productGroup])
					->where('page_content.data_name = \'value\'')
			)
		;

		// Pages with no options set should also show if they have sale units
		$pageSubQuery
			->where(
				'(' . $contentAlias . '_product.value_string IN (:productIDs?ij)' . PHP_EOL .
				'AND ' . $contentAlias . '_product_option_name.value_string = \'\'' . PHP_EOL .
				'AND ' . $contentAlias . '_product_option_value.value_string = \'\')', [
					'productIDs' => $productIDs,
				], false);

		// Sale constraints
		foreach ($constraints as $constraint) {
			$pageSubQuery
				->where(
					'(' . $contentAlias . '_product.value_string = :productID?i' . PHP_EOL .
					'AND ' . $contentAlias . '_product_option_name.value_string IN (:names?sj)' . PHP_EOL .
					'AND ' . $contentAlias . '_product_option_value.value_string IN (:values?sj))', [
						'productID' => $constraint['id'],
						'names'  => $constraint['names'],
						'values' => $constraint['values'],
					], false);
		}

		$queryBuilder
			->leftJoin($contentAlias, $this->_getJoinStatement(), 'page_content')
			->where('page.page_id IN (?q)', [$pageSubQuery])
		;

	}
}
