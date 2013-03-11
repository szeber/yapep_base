<?php

namespace YapepBase\Database;
use YapepBase\Config;
use YapepBase\Database\DbFactory;

/**
 * DbFactory test case.
 */
class DbFactoryTest extends \YapepBase\BaseTest {

	/**
	 * Config instance
	 *
	 * @var \YapepBase\Config
	 */
	protected $config;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();
		$this->config = Config::getInstance();
		$this->config->set(array(
			'resource.database.test.rw.backendType' => DbFactory::BACKEND_TYPE_SQLITE,
			'resource.database.test.rw.path'        => ':memory:',

		));
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		if (!empty($this->config)) {
			$this->config->clear();
		}
		$this->config = null;
		DbFactory::clearConnections();
		parent::tearDown();
	}

	/**
	 * Tests if a successful connection can be made, and if connections to the same name produce the same instance.
	 */
	public function testSuccessfulConnection() {
		$connection = DbFactory::getConnection('test', DbFactory::TYPE_READ_WRITE);
		$this->assertInstanceOf('\YapepBase\Database\DbConnection', $connection, 'Invalid connection type');
		$connection2 = DbFactory::getConnection('test', DbFactory::TYPE_READ_WRITE);
		$this->assertSame($connection, $connection2, 'The returned instances are not the same');
	}

	/**
	 * Tests if a connection with a bad connection name fails.
	 */
	public function testBadConnectionName() {
		$this->setExpectedException('\YapepBase\Exception\DatabaseException',
			'Database connection configuration "test2" not found');

		DbFactory::getConnection('test2', DbFactory::TYPE_READ_WRITE);
	}

	/**
	 * Tests if the factory produces an error on trying to create a connection with a typo in the config.
	 */
	public function testBadConnectionConfig() {
		$this->setExpectedException('\YapepBase\Exception\DatabaseException', 'Invalid database config');

		$this->config->set(array(
			'resource.database.test2.rw.backendType' => DbFactory::BACKEND_TYPE_SQLITE,
			'resource.database.test2.rw.pah'        => ':memory:',
		));

		DbFactory::getConnection('test2', DbFactory::TYPE_READ_WRITE);
	}

	/**
	 * Tests if the factory produces an error on trying to create a connection without a backendType.
	 */
	public function testMissingConnectionName() {
		$this->setExpectedException('\YapepBase\Exception\DatabaseException', 'Invalid database config');

		$this->config->set(array(
			'resource.database.test2.rw.path'        => ':memory:',
		));

		DbFactory::getConnection('test2', DbFactory::TYPE_READ_WRITE);
	}

	/**
	 * Tests if the factory produces an error on trying to create a connection to an invalid backend.
	 */
	public function testBadConnectionBackendType() {
		$this->setExpectedException('\YapepBase\Exception\DatabaseException', 'Invalid database config');

		$this->config->set(array(
			'resource.database.test2.rw.backendType' => 'invalid',
		));

		DbFactory::getConnection('test2', DbFactory::TYPE_READ_WRITE);
	}

}

