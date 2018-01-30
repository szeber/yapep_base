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

class GetFromStorageTest extends TestAbstract {

	protected $prefix = 'prefix';
	protected $key    = 'key';


	public function testWhenEmptyKeyGiven_shouldThrowException() {
		$bo = new MockBo($this->prefix);

		$this->expectException('\YapepBase\Exception\ParameterException');
		$this->expectExceptionMessage('Given key should not be empty');
		$bo->getFromStorage('');
	}


	public function testWhenCalledProperly_shouldReturnDataFromStore() {
		$bo = new MockBo($this->prefix);
		$this->expectGetFromStorage($bo, 'test');

		$result = $bo->getFromStorage($this->key);

		$this->assertEquals('test', $result);
	}

	protected function expectGetFromStorage(MockBo $bo, $expectedResult) {
		$this->storage
			->shouldReceive('get')
			->with($bo->getKeyWithPrefix($this->key))
			->once()
			->andReturn($expectedResult);
	}
}
