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

class SetToStorageTest extends TestAbstract {

	protected $prefix = 'prefix';
	protected $key    = 'key';
	protected $ttl    = 1;


	public function testWhenEmptyKeyGiven_shouldThrowException() {
		$bo = new MockBo($this->prefix);

		$this->expectException('\YapepBase\Exception\ParameterException');
		$this->expectExceptionMessage('Given key should not be empty');
		$bo->setToStorage('', array());
	}


	public function noStoreDataProvider() {
		return array(
			'not forced and data is empty array' => array(false, array()),
			'not forced and data is null'        => array(false, array()),
			'not forced and data is 0'           => array(false, array()),
			'not forced and data is false'       => array(false, array()),
			'forced and data is false'           => array(true, false)
		);
	}

	/**
	 * @dataProvider noStoreDataProvider
	 *
	 * @param bool  $forceEmptyStorage
	 * @param mixed $data
	 */
	public function testWhenDataShouldNotBeStored_nothingShouldHappen($forceEmptyStorage, $data) {
		$bo = new MockBo($this->prefix);

		$bo->setToStorage($this->key, $data, $this->ttl, $forceEmptyStorage);
	}

	public function storeDataProvider() {
		return array(
			'forced to store and data is empty' => array(true, ''),
			'not forced and data is not empty'  => array(false, 'value'),
		);
	}

	/**
	 * @dataProvider storeDataProvider
	 *
	 * @param bool  $forceEmptyStorage
	 * @param mixed $data
	 */
	public function testWhenDataAndKeyShouldBeStored_shouldStoreEverythingProperly($forceEmptyStorage, $data) {
		$bo = new MockBo($this->prefix);
		$this->enableKeyStoring();

		$this->expectSetToStorage($bo->getKeyWithPrefix($this->key), $data, $this->ttl);
		$this->expectKeyAdd($bo);

		$bo->setToStorage('key', $data, 1, $forceEmptyStorage);
	}

	protected function expectKeyAdd(MockBo $bo) {
		$bo->setCurrentTime(1);
		$keyForKeys = $bo->getKeyWithPrefix(BoAbstract::CACHE_KEY_FOR_KEYS_SUFFIX);

		$this->storage
				->shouldReceive('get')
				->with($keyForKeys)
				->once()
				->andReturn(array())
			->getMock()
				->shouldReceive('set')
				->with(
					$keyForKeys,
					array($this->key => $this->ttl + 1),
					BoAbstract::CACHE_KEY_FOR_KEYS_TTL
				)
				->once();
	}

}
