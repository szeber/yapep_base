<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Mock\BusinessObject
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\BusinessObject;

use YapepBase\BusinessObject\BoAbstract;
use YapepBase\Exception\Exception;

/**
 * Mock object for business objects
 *
 * @package    YapepBase
 * @subpackage Mock\BusinessObject
 */
class MockBo extends BoAbstract {

	protected $currentTime;

	public function getFromStorage($key) {
		return parent::getFromStorage($key);
	}

	public function setToStorage($key, $data, $ttl = 0, $forceEmptyStorage = false) {
		parent::setToStorage($key, $data, $ttl, $forceEmptyStorage);
	}

	public function deleteFromStorage($key = '') {
		parent::deleteFromStorage($key);
	}

	public function getKeyPrefix($withDotSuffix = true) {
		return parent::getKeyPrefix($withDotSuffix);
	}

	public function getKeyWithPrefix($key) {
		return parent::getKeyWithPrefix($key);
	}

	protected function getCurrentTime() {
		if (is_null($this->currentTime)) {
			throw new Exception('Current Time requested but not set!');
		}

		return $this->currentTime;
	}

	public function setCurrentTime($currentTime) {
		$this->currentTime = $currentTime;
	}

}
