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

/**
 * MemcacheMock class
 *
 * @package    YapepBase
 * @subpackage Test\Mock\Storage
 * @codeCoverageIgnore
 */
class MemcachedMock {

	const OPERATION_GET = 'get';
	const OPERATION_SET = 'set';
	const OPERATION_DEL = 'del';

	public $serverList = array();
	public $port;
	public $connectionSuccessful = true;
	public $data = array();
	public $resultCode;
	public $resultMessage;
	protected $lastOperation;

	public function getServerList() {
		return $this->serverList;
	}

	public function addServer($host, $port) {
		$this->serverList[] = array(
			'host' => $host,
			'port' => $port,
		);
	}

	public function set($key, $var, $expire) {
		$data = array(
			'value' => $var,
			'ttl'   => $expire,
		);
		$dataIsSame =  (isset($this->data[$key]) && $this->data[$key] === $data);
		$this->data[$key] = $data;
		$this->lastOperation = self::OPERATION_SET;
		return !$dataIsSame;
	}

	public function get($key) {
		$this->lastOperation = self::OPERATION_GET;
		return (isset($this->data[$key]) ? $this->data[$key]['value'] : false);
	}

	public function delete($key) {
		$this->lastOperation = self::OPERATION_DEL;
		if (isset($this->data[$key])) {
			unset($this->data[$key]);
			return true;
		}
		return false;
	}

	public function getResultCode() {
		if (!empty($this->resultCode)) {
			return $this->resultCode;
		}
		switch ($this->lastOperation) {
			case self::OPERATION_SET:
				$code = \Memcached::RES_NOTSTORED;
				break;

			case self::OPERATION_DEL:
				// Same as get
			case self::OPERATION_GET:
				$code = \Memcached::RES_NOTFOUND;
				break;

			default:
				$code = \Memcached::RES_SERVER_ERROR;
				break;
		}
		return $code;
	}

	public function getResultMessage() {
		if (!empty($this->resultMessage)) {
			return $this->resultMessage;
		}
		switch ($this->lastOperation) {
			case self::OPERATION_SET:
				$message = 'Not set';
				break;

			case self::OPERATION_DEL:
				// Same as get
			case self::OPERATION_GET:
				$message = 'Not found';
				break;

			default:
				$message = 'Unknown operation';
				break;
		}
		return $message;
	}
}