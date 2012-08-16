<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Mock\Storage
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Storage;

/**
 * MemcacheMock class
 *
 * @package    YapepBase
 * @subpackage Test\Mock\Storage
 * @codeCoverageIgnore
 */
class MemcacheMock {

	public $host;
	public $port;
	public $connectionSuccessful = true;
	public $data = array();

	public function connect($host, $port) {
		$this->host = $host;
		$this->port = $port;
		return (bool)$this->connectionSuccessful;
	}

	public function set($key, $var, $flag, $expire) {
		$this->data[$key] = array(
			'value' => $var,
			'flag'  => $flag,
			'ttl'   => $expire,
		);
	}

	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key]['value'] : false);
	}

	public function delete($key) {
		if (isset($this->data[$key])) {
			unset($this->data[$key]);
		}
	}
}