<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Mock\Storage
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Test\Mock\Storage;
use YapepBase\Exception\ParameterException;

use YapepBase\Storage\IStorage;

/**
 * StorageMock class
 *
 * @package    YapepBase
 * @subpackage Test\Mock\Storage
 */
class StorageMock implements IStorage {

    protected $ttlSupport;
    protected $persistent;
    protected $data;

    public function __construct($ttlSupport, $persistent, array $data = array()) {
        $this->ttlSupport = $ttlSupport;
        $this->persistent = $persistent;
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

	/**
	 * (non-PHPdoc)
     * @see YapepBase\Storage.IStorage::set()
     */
    public function set($key, $data, $ttl = 0) {
        if ($ttl != 0 && !$this->ttlSupport) {
            throw new ParameterException('TTL option is set, when we have no TTL support.');
        }

        $this->data[$key] = $data;
    }
    /**
	 * (non-PHPdoc)
     * @see YapepBase\Storage.IStorage::delete()
     */
    public function delete($key) {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

	/**
	 * (non-PHPdoc)
     * @see YapepBase\Storage.IStorage::get()
     */
    public function get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return false;
    }

	/**
	 * (non-PHPdoc)
     * @see YapepBase\Storage.IStorage::isPersistent()
     */
    public function isPersistent() {
        return $this->persistent;
    }

	/**
	 * (non-PHPdoc)
     * @see YapepBase\Storage.IStorage::isTtlSupported()
     */
    public function isTtlSupported() {
        return $this->ttlSupport;
    }

}