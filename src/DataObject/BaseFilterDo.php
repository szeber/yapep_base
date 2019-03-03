<?php
/**
 * @package    YapepBase
 * @subpackage DataObject
 */

namespace YapepBase\DataObject;

use YapepBase\Exception\ParameterException;

/**
 * Do filter base class, which should be extended by every filter related DO class.
 *
 * Provides basic features for handling filters (like ordering, pagination, etc...)
 *
 * @package    YapepBase
 * @subpackage DataObject
 */
class BaseFilterDo {

	/** @var array   Stores the list of the fields usable by the filter. */
	protected $usableFields;

	/** @var int   Which page should be returned. */
	protected $page;
	/** @var int   How many items should be included in the result. */
	protected $itemsPerPage;

	/** @var string   The name of the field for ordering the result. */
	protected $order;
	/** @var string   The direction of the ordering. */
	protected $orderDirection;

	/**
	 * Constructor.
	 *
	 * @param array $fields   The fields of the table(s) which can be used for filtering and ordering.
	 */
	public function __construct(array $fields) {
		$this->usableFields = $fields;
	}

	/**
	 * Sets the pagenumber.
	 *
	 * @param int $page   Which page should be returned.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the page number is not valid.
	 */
	public function setPage($page) {
		if ($page < 1) {
			throw new ParameterException();
		}

		$this->page = $page;
	}

	/**
	 * Sets the itemnumber.
	 *
	 * @param int $itemsPerPage   How may items should be included on one page.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the given number is not valid.
	 */
	public function setItemsPerPage($itemsPerPage) {
		if ($itemsPerPage < 1) {
			throw new ParameterException();
		}

		$this->itemsPerPage = $itemsPerPage;
	}

	/**
	 * Sets the order of the list.
	 *
	 * @param string $order   The name of the field for ordering.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the given field doesn't exist.
	 */
	public function setOrder($order) {
		if (!in_array($order, $this->usableFields)) {
			throw new ParameterException('Invalid field for ordering: ' . $order);
		}
		$this->order = $order;
	}

	/**
	 * Sets the direction of the order.
	 *
	 * @param int $direction   The direction (-1 for descending, 1 for ascending).
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the given direction doesn't exist.
	 */
	public function setDirection($direction) {
		if ($direction == -1) {
			$this->orderDirection = 'DESC';
		}
		elseif ($direction == 1) {
			$this->orderDirection = 'ASC';
		}
		else {
			throw new ParameterException();
		}
	}

	/**
	 * Returns the order field.
	 *
	 * @return string
	 */
	public function getOrder() {
		return $this->order;
	}

	/**
	 * Returns the order direction.
	 *
	 * @return string
	 */
	public function getOrderDirection() {
		return $this->orderDirection;
	}

	/**
	 * Returns the page.
	 *
	 * @return int
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * Returns the items per page.
	 *
	 * @return int
	 */
	public function getItemsPerPage() {
		return $this->itemsPerPage;
	}

	/**
	 * Returns an unique id based on the filters stored in the object.
	 *
	 * The id can be used for caching for example.
	 *
	 * @return string   The unique string.
	 *
	 * @throws \YapepBase\Exception\Exception   If something went wrong in the reflection processing.
	 */
	public function getId() {
		$reflectionClass = new \ReflectionClass($this);
		$properties = $reflectionClass->getProperties();

		$propertiesString = '';
		foreach ($properties as $reflectionProperty) {
			try {
				$reflectionProperty->setAccessible(true);
				$name = $reflectionProperty->getName();
				$value = $reflectionProperty->getValue($this);

				if (is_array($value)) {
					$value = implode('|', $value);
				}
				$propertiesString .= $name . '_' . $value . '_';
			}
			catch (\Exception $e) {
				throw new \YapepBase\Exception\Exception($e->getMessage(), $e->getCode(), $e);
			}
		}

		return $propertiesString;
	}
}