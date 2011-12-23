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

    public $serverList = array();
    public $port;
    public $connectionSuccessful = true;
    public $data = array();

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
        $this->data[$key] = array(
            'value' => $var,
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