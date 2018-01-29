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

use Mockery;
use YapepBase\Application;
use YapepBase\Config;
use YapepBase\DependencyInjection\SystemContainer;

/**
 * Common ancestor for BoAbstract tests
 */
class TestAbstract extends \YapepBase\BaseTest {

	/** @var SystemContainer */
	protected $originalDiContainer;

	/** @var \Mockery\MockInterface */
	protected $storage;

	/** @var string */
	protected $projectName = 'test';


	protected function setUp() {
		parent::setUp();
		$this->originalDiContainer = Application::getInstance()->getDiContainer();
		$this->setupStorage();
	}

	protected function tearDown() {
		parent::tearDown();
		Application::getInstance()->setDiContainer($this->originalDiContainer);
		Config::getInstance()->clear();
	}

	private function setupStorage() {
		$diContainer = new SystemContainer();

		$this->storage = Mockery::mock('\YapepBase\Storage\IStorage');

		$diContainer->setMiddlewareStorage($this->storage);
		Application::getInstance()->setDiContainer($diContainer);
	}


	protected function enableKeyStoring() {
		Config::getInstance()->set('resource.storage.middleware.isKeyStoringEnabled', true);
	}


	protected function expectProjectNameRequested() {
		Config::getInstance()->set(array('system.project.name' => $this->projectName));
	}


	protected function expectSetToStorage($key, $data, $ttl) {
		$this->storage
			->shouldReceive('set')
			->with($key, $data, $ttl)
			->once();
	}
}
