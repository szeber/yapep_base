<?php
/**
 * This file is part of YAPEPBase. It was merged from janoszen's Alternate-Class-Repository project.
 *
 * @package      YapepBase
 * @subpackage   Util
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Util;
use YapepBase\Exception\IndexOutOfBoundsException;

/**
 * This class implements a common array handling for \YapepBase\Util\Collection. No other classes should implement this,
 * because it will most likely not prevail if parts are reimplemented in C.
 *
 * @package      YapepBase
 * @subpackage   Util
 * @internal
 */
abstract class ArrayObject implements \Iterator, \ArrayAccess, \Countable, \Serializable {

	/**
	 * Stores the collection elements
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Throws a \YapepBase\Exception\ValueException, if $offset is not of the required type
	 *
	 * @param mixed $offset   The offset.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ValueException if $offset is not of the required type
	 */
	abstract protected function keyCheck($offset);

	/**
	 * This function checks, if the ArrayObject subclass may contain the given type.
	 *
	 * @param mixed $element   The element to check.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\TypeException if the Collection cannot contain this element type.
	 *
	 * @codeCoverageIgnore
	 */
	protected function typeCheck($element) {}

	/**
	 * Create a new data object
	 *
	 * @param array $data   The data to check.
	 */
	public function __construct($data = null) {
		if (\is_array($data)) {
			foreach ($data as $key => $value) {
				$this->offsetSet($key, $value);
			}
		} elseif (!\is_null($data)) {
			$this->offsetSet(null, $data);
		}
	}

	/**
	 * Counts the elements in this collection.
	 *
	 * Implemented as required by the Countable interface.
	 *
	 * @return int
	 */
	public function count() {
		return count($this->data);
	}

	/**
	 * Returns if an offset exists in this collection.
	 *
	 * Implemented as required by the ArrayAccess interface.
	 *
	 * @param int $offset   The offset to check.
	 *
	 * @return bool
	 *
	 * @throws \YapepBase\Exception\ValueException   If $offset is not an int.
	 */
	public function offsetExists($offset) {
		$this->keyCheck($offset);
		if (array_key_exists($offset, $this->data)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns an element in this collection.
	 *
	 * Implemented as required by the ArrayAccess interface.
	 *
	 * @param int $offset   The offset to fetch.
	 *
	 * @return mixed
	 *
	 * @throws \YapepBase\Exception\ValueException              If the offset is not an int.
	 * @throws \YapepBase\Exception\IndexOutOfBoundsException   If the offset does not exist.
	 */
	public function offsetGet($offset) {
		$this->keyCheck($offset);
		if ($this->offsetExists($offset)) {
			return $this->data[$offset];
		} else {
			throw new IndexOutOfBoundsException($offset);
		}
	}

	/**
	 * Set an element in this Collection.
	 *
	 * Implemented as required by the ArrayAccess interface.
	 *
	 * @param int|string $offset   The offset.
	 * @param mixed      $value    The value.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->typeCheck($value);
		if (\is_null($offset)) {
			$this->data[] = $value;
		} else {
			$this->keyCheck($offset);
			$this->data[$offset] = $value;
		}
	}

	/**
	 * This function unsets a given offset from a Collection.
	 *
	 * Implemented as required by the ArrayAccess interface.
	 *
	 * @param int $offset   The offset.
	 *
	 * @return \YapepBase\Util\Collection
	 *
	 * @throws \YapepBase\Exception\IndexOutOfBoundsException
	 */
	public function offsetUnset($offset) {
		$this->keyCheck($offset);
		if ($this->offsetExists($offset)) {
			unset($this->data[$offset]);
			return $this;
		} else {
			throw new IndexOutOfBoundsException($offset);
		}
	}

	/**
	 * Returns the current element in the Collection.
	 *
	 * Implemented as required by the Iterator interface.
	 *
	 * @return mixed
	 *
	 * @throws \YapepBase\Exception\IndexOutOfBoundsException   If the current element is not valid
	 */
	public function current() {
		if (!$this->valid()) {
			throw new IndexOutOfBoundsException();
		} else {
			return current($this->data);
		}
	}

	/**
	 * Returns the key of the current element. Returns the key of the current Collection element.
	 *
	 * Implemented as required by the Iterator interface.
	 *
	 * @return int|string
	 */
	public function key() {
		return key($this->data);
	}

	/**
	 * Returns the current element and moves the pointer to the next element.
	 *
	 * Implemented as required by the Iterator interface.
	 *
	 * @return mixed
	 *
	 * @throws \YapepBase\Exception\IndexOutOfBoundsException if the pointer is at the end of the
	 *  Collection
	 */
	public function next() {
		$current = each($this->data);
		if ($current === false) {
			throw new IndexOutOfBoundsException();
		} else {
			return $current['value'];
		}
	}

	/**
	 * Sets the internal pointer of this collection to the first element.
	 *
	 * Implemented as required by the Iterator interface.
	 *
	 * @return bool false if the Collection is empty.
	 */
	public function rewind() {
		if (reset($this->data)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if the Collection internal pointer is on a valid element.
	 *
	 * Implemented as required by the Iterator interface.
	 *
	 * @return bool
	 */
	public function valid() {
		if ($this->key() === null) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Serializes this Collection as required by the Serializable interface.
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize($this->data);
	}

	/**
	 * Loads this Collection from a serialized string. Does NOT do a typeCheck.
	 *
	 * @param string $serialized   The serialized string.
	 *
	 * @return void
	 */
	public function unserialize($serialized) {
		$this->data = unserialize($serialized);
	}

	/**
	 * Add a single element
	 *
	 * @param mixed $element   The element.
	 *
	 * @return \YapepBase\Util\ArrayObject
	 */
	public function add($element) {
		$this->typeCheck($element);
		$this[] = $element;
		return $this;
	}

	/**
	 * Add all elements from an other collection to this one.
	 *
	 * @param \YapepBase\Util\ArrayObject $collection   The collection.
	 *
	 * @return \YapepBase\Util\ArrayObject Returns this Collection in a consistent manner.
	 *
	 * @throws \YapepBase\Exception\TypeException   If an element fails the typeCheck.
	 */
	public function addAll(ArrayObject $collection) {
		/**
		 * Add a typecheck to return in a consistent manner, if it fails.
		 */
		foreach ($collection as $element) {
			$this->typeCheck($element);
		}
		foreach ($collection as $element) {
			$this[] = $element;
		}
		return $this;
	}

	/**
	 * Deletes all elemens.
	 *
	 * @return void
	 */
	public function clear() {
		$this->data = array();
	}

	/**
	 * Checks if an element is contained here.
	 *
	 * @param mixed $element   The element.
	 *
	 * @return bool
	 */
	public function contains($element) {
		return in_array($element, $this->data);
	}

	/**
	 * Checks all elements in a Collection if they are contained in this ArrayObject.
	 *
	 * @param \YapepBase\Util\ArrayObject $other   The other collection.
	 *
	 * @return bool
	 */
	public function containsAll(ArrayObject $other) {
		foreach ($other as $element) {
			if (!$this->contains($element)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Removes an element by reference.
	 *
	 * @param mixed $element   The element.
	 *
	 * @return bool if the data has changed.
	 */
	public function remove($element) {
		$key = array_search($element, $this->data);
		if ($key === false) {
			return false;
		} else {
			unset($this->data[$key]);
			return true;
		}
	}

	/**
	 * Remove all elements contained in an other ArrayObject
	 *
	 * @param \YapepBase\Util\ArrayObject $other   The other object.
	 *
	 * @return \YapepBase\Util\ArrayObject
	 */
	public function removeAll(ArrayObject $other) {
		foreach ($other as $elements) {
			$this->remove($elements);
		}
		return $this;
	}

	/**
	 * Retains all elements contained in a different collection
	 *
	 * @param \YapepBase\Util\ArrayObject $other   The other collection.
	 *
	 * @return \YapepBase\Util\ArrayObject
	 */
	public function retainAll(ArrayObject $other) {
		foreach ($this as $key => $value) {
			if (!$other->contains($value)) {
				unset($this[$key]);
			}
		}
		return $this;
	}
}
