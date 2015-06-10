<?php

namespace Message\Mothership\Ecommerce\Filter;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Mothership\CMS\Page\Filter\AbstractContentFilter;

class SaleFilter extends AbstractContentFilter
{
	protected $_productGroup      = 'product';
	protected $_productFieldName  = 'product';
	protected $_optionFieldName   = 'option';

	private $_queryBuilderFactory;

	public function __construct($name, $displayName, QueryBuilderFactory $queryBuilderFactory)
	{
		$this->_queryBuilderFactory =  $queryBuilderFactory;
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
		$contentAlias = $this->_getContentAlias();
		$productAlias = $contentAlias . '_product';
		$unitAlias    = $contentAlias . '_unit';
		$priceAlias   = $contentAlias . '_unit_price';

		$queryBuilder
			->leftJoin($contentAlias, $this->_getJoinStatement(), 'page_content')
			// join on products
			->leftJoin($productAlias, $productAlias . '.product_id = ' . $contentAlias . '.value_int AND ' 
				. $contentAlias . '.field_name = \'' . (int) $this->_productFieldName . '\'', 'product')
			// join on units
			->leftJoin($unitAlias, "$productAlias.product_id = $unitAlias.product_id" , 'product_unit')
			->leftJoin($priceAlias, "$priceAlias.unit_id = $unitAlias.unit_id AND $priceAlias.currency_id = 'GBP'" , 'product_unit_price') // TODO: pass currency in somehow
			// ->where()
		;

		de($queryBuilder->getQueryString());

	}
}