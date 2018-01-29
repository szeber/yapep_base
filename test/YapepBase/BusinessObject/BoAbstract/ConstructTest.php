<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   BusinessObject\BoAbstract
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\BusinessObject\BoAbstract;


use YapepBase\Mock\BusinessObject\MockBo;

class ConstructTest extends TestAbstract {


	public function testWhenNoKeyPrefixGiven_shouldUseProjectName() {
		$this->expectProjectNameRequested();

		$bo = new MockBo();
		$keyPrefix = $bo->getKeyPrefix();

		$this->assertEquals('test.YapepBase\Mock\BusinessObject\MockBo', $keyPrefix);
	}

	public function testWhenKeyPrefixGiven_shouldUseGivenPrefix() {
		$bo = new MockBo('prefix');
		$keyPrefix = $bo->getKeyPrefix();

		$this->assertEquals('prefix', $keyPrefix);
	}
}
