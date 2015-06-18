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
		$this->_productGroup = $group;

		return $this;
	}

	/**
	 * Sets the name of the field to check for the product
	 */
	public function setProductField($field)
	{
		$this->_productFieldName = $field;
		
		return $this;
	}

	/**
	 * Sets the name of the field to check for the product option
	 */
	public function setOptionField($field)
	{
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

		$saleProductIDs = [];
		$saleOptionNames = [];
		$saleOptionValues = [];

		// Build sale product variables for the query
		foreach ($saleUnits as $unit) {
			$saleProductIDs[$unit->getProduct()->id] = $unit->getProduct()->id;

			foreach($unit->getOptions() as $name => $value) {
				$saleOptionNames[$name] = $name;
				$saleOptionValues[$value] = $value;
			}
		}

		$queryBuilderFactory = $this->_queryBuilderFactory;

		$subQuery = function() use ($queryBuilderFactory) {
			return $queryBuilderFactory->getQueryBuilder();
		};

		$contentAlias = $this->_getContentAlias();

		// Subquery to select all pages with the wrong content fields to exclude.
		// This is chosen over selecting the pages with the correct fields as
		// we want to include pages with only a product and no options too.
		$pageSubQuery = $subQuery()
			->select($contentAlias . '_product.page_id')
			->from(
				$contentAlias . '_product',
				$subQuery()
					->select('page_id')
					->from('page_content')
					->where('page_content.field_name = ?s', [$this->_optionFieldName])
					->where('page_content.group_name = ?s', [$this->_productGroup])
					->where('page_content.data_name = \'name\'')
					->where('page_content.value_string IN (?sj)', [$saleOptionNames])
			)
			->join(
				$contentAlias . '_product_option_value',
				$contentAlias . '_product_option_value.page_id = ' . $contentAlias . '_product.page_id',
				$subQuery()
					->select('page_id')
					->from('page_content')
					->where('page_content.field_name = ?s', [$this->_optionFieldName])
					->where('page_content.group_name = ?s', [$this->_productGroup])
					->where('page_content.data_name = \'value\'')
					->where('page_content.value_string NOT IN (?sj)', [$saleOptionValues])
			)
		;

		$queryBuilder
			->leftJoin($contentAlias, $this->_getJoinStatement(), 'page_content')
			// Exclude pages with incorrect options
			->where('page.page_id NOT IN (?q)', [$pageSubQuery])
			// Exclude pages with incorrect product
			->where('page.page_id NOT IN (?q)', [
				$subQuery()
				->select('page_id')
				->from('page_content')
				->where('page_content.field_name = ?s', [$this->_productFieldName])
				->where('page_content.group_name = ?s', [$this->_productGroup])
				->where('page_content.value_string NOT IN (?ij)', [$saleProductIDs])
			])
		;

	}
}
