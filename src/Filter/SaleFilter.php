<?php

namespace Message\Mothership\Ecommerce\Filter;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Mothership\CMS\Page\Filter\AbstractContentFilter;
use Message\Mothership\Commerce\Product\Unit\Loader as UnitLoader;
use Message\Mothership\CMS\Page\Filter\ContentFilter;
use Message\Cog\DB\QueryBuilderFactory;

class SaleFilter extends AbstractContentFilter
{
	protected $_productGroup      = 'product';
	protected $_productFieldName  = 'product';
	protected $_optionFieldName   = 'option';

	protected $_unitLoader;
	protected $_queryBuilderFactory;

	public function __construct($name, $displayName, UnitLoader $unitLoader, QueryBuilderFactory $queryBuilderFactory)
	{
		parent::__construct($name, $displayName);

		$this->_unitLoader =  $unitLoader;
		$this->_queryBuilderFactory = $queryBuilderFactory;
	}

	public function setGroup($group)
	{
		$this->_productGroup = $group;

		return $this;
	}

	public function setProductField($field)
	{
		$this->_productFieldName = $field;
		
		return $this;
	}

	public function setOptionField($field)
	{
		$this->_optionFieldName = $field;

		return $this;
	}

	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{
		if (empty($this->_value)) {
			return;
		}

		$saleUnits = $this->_unitLoader->getSaleUnits();

		$saleProductIDs = [];
		$saleOptionNames = [];
		$saleOptionValues = [];
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

		$pageSubQuery = $subQuery()
			->select($contentAlias . '_product.page_id')
			->from(
				$contentAlias . '_product',
				$subQuery()
					->select('page_id')
					->from('page_content')
					->where('page_content.field_name = ?s', [$this->_productFieldName])
					->where('page_content.group_name = ?s', [$this->_productGroup])
					->where('page_content.value_string IN (?ij)', [$saleProductIDs])
			)
			->join(
				$contentAlias . '_product_option_name',
				$contentAlias . '_product_option_name.page_id = ' . $contentAlias . '_product.page_id',
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
					->where('page_content.value_string IN (?sj)', [$saleOptionValues])
			)
		;

		$queryBuilder
			->leftJoin($contentAlias, $this->_getJoinStatement(), 'page_content')
			->where('page.page_id IN (?q)', [$pageSubQuery]);

	}
}