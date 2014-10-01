<?php

namespace Message\Mothership\Ecommerce\ProductPage\UploadRecord;

class UploadRecord
{
	/**
	 * @var int
	 */
	private $_pageID;

	/**
	 * @var int
	 */
	private $_productID;

	/**
	 * @var string
	 */
	private $_pageTitle;

	/**
	 * @var int
	 */
	private $_unitID;

	/**
	 * @var \DateTime
	 */
	private $_confirmedAt;

	/**
	 * @var int
	 */
	private $_confirmedBy;

	/**
	 * @param int $pageID
	 *
	 * @return UploadRecord         return $this for chainability
	 */
	public function setPageID($pageID)
	{
		$this->_pageID = $pageID;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPageID()
	{
		return $this->_pageID;
	}

	/**
	 * @param int $productID
	 *
	 * @return UploadRecord         return $this for chainability
	 */
	public function setProductID($productID)
	{
		$this->_productID = $productID;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getProductID()
	{
		return $this->_productID;
	}

	/**
	 * @param string $pageTitle
	 *
	 * @return UploadRecord         return $this for chainability
	 */
	public function setPageTitle($pageTitle)
	{
		$this->_pageTitle = $pageTitle;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPageTitle()
	{
		return $this->_pageTitle;
	}

	/**
	 * @param int $unitID
	 *
	 * @return UploadRecord         return $this for chainability
	 */
	public function setUnitID($unitID)
	{
		$this->_unitID = $unitID;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getUnitID()
	{
		return $this->_unitID;
	}

	/**
	 * @param \DateTime $confirmedAt
	 *
	 * @return UploadRecord         return $this for chainability
	 */
	public function setConfirmedAt($confirmedAt)
	{
		$this->_confirmedAt = $confirmedAt;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getConfirmedAt()
	{
		return $this->_confirmedAt;
	}

	/**
	 * @param int $confirmedBy
	 *
	 * @return UploadRecord         return $this for chainability
	 */
	public function setConfirmedBy($confirmedBy)
	{
		$this->_confirmedBy = $confirmedBy;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getConfirmedBy()
	{
		return $this->_confirmedBy;
	}


}