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


use YapepBase\BusinessObject\BoAbstract;
use YapepBase\Mock\BusinessObject\MockBo;

class DeleteFromStorageTest extends TestAbstract {

	protected $prefix = 'prefix';

	public function testWhenKeysAreStoredButEmpty_shouldDoNothing() {
		$this->enableKeyStoring();

		$bo = new MockBo($this->prefix);

		$this->expectGetKeys($bo, array());

		$bo->deleteFromStorage('key');
	}

	public function testWhenKeysAreStoredAndEmptyKeyGiven_shouldDeleteEverything() {
		$this->enableKeyStoring();

		$bo = new MockBo($this->prefix);

		$this->expectGetKeys($bo, array('key1' => 1, 'key2' => 2));
		$this->expectedDeleteFromStorage($bo, 'key1');
		$this->expectedDeleteFromStorage($bo, 'key2');
		$this->expectKeysRewritten($bo, array());

		$bo->deleteFromStorage('');
	}

	public function testWhenKeysAreStoredAndKeyWithAsterixGiven_shouldDeleteEverythingMatches() {
		$this->enableKeyStoring();

		$bo = new MockBo($this->prefix);

		$this->expectGetKeys($bo, array('first.1' => 1, 'first.2' => 2, 'second.1' => 3));
		$this->expectedDeleteFromStorage($bo, 'first.1');
		$this->expectedDeleteFromStorage($bo, 'first.2');
		$this->expectKeysRewritten($bo, array('second.1' => 3));

		$bo->deleteFromStorage('first.*');
	}

	public function testWhenKeysAreNotStored_shouldJustDeleteGivenEntries() {
		$bo = new MockBo($this->prefix);

		$this->expectedDeleteFromStorage($bo, 'key');

		$bo->deleteFromStorage('key');
	}


	protected function expectGetKeys(MockBo $bo, $expectedResult) {
		$this->storage
			->shouldReceive('get')
			->with($bo->getKeyWithPrefix(BoAbstract::CACHE_KEY_FOR_KEYS_SUFFIX))
			->once()
			->andReturn($expectedResult);
	}

	protected function expectedDeleteFromStorage(MockBo $bo, $key) {
		$this->storage
			->shouldReceive('delete')
			->with($bo->getKeyWithPrefix($key))
			->once();
	}

	protected function expectKeysRewritten(MockBo $bo, $expectedKeys) {
		$this->expectSetToStorage(
			$bo->getKeyWithPrefix(BoAbstract::CACHE_KEY_FOR_KEYS_SUFFIX),
			$expectedKeys,
			BoAbstract::CACHE_KEY_FOR_KEYS_TTL
		);
	}
}
