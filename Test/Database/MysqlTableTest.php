<?php

namespace YapepBase\Test\Database;

use YapepBase\Config;
use YapepBase\Database\DbFactory;
use YapepBase\Database\DbTable;

use YapepBase\Test\Mock\Database\TestTableMysqlMock;

/**
 * MysqlTable test case.
 */
class MysqlTableTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Config instance
	 *
	 * @var \YapepBase\Config
	 */
	protected $config;

	/**
	 * Mysql connection.
	 *
	 * @var \YapepBase\Database\DbConnection
	 */
	protected $connection;

	/**
	 * @var bool   Is the test runnable.
	 */
	protected $isRunnable = true;

	/**
	 * Cosntructor
	 */
	public function __construct() {
		$rwHost = getenv('YAPEPBASE_TEST_MYSQL_RW_HOST');
		$roHost = getenv('YAPEPBASE_TEST_MYSQL_RO_HOST');

		// TODO: You have to configure the previous ENV vairables on order to make this test work [emul]
		if (empty($rwHost) || empty($roHost)) {
			$this->isRunnable = false;
		}

		parent::__construct();
	}

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		if (!$this->isRunnable) {
			$this->markTestSkipped('Required ENV variables missing');
			return;
		}

		$rwHost     = getenv('YAPEPBASE_TEST_MYSQL_RW_HOST');
		$rwUser     = getenv('YAPEPBASE_TEST_MYSQL_RW_USER');
		$rwPassword = getenv('YAPEPBASE_TEST_MYSQL_RW_PASSWORD');
		$rwDatabase = getenv('YAPEPBASE_TEST_MYSQL_RW_DATABASE');

		$roHost     = getenv('YAPEPBASE_TEST_MYSQL_RO_HOST');
		$roUser     = getenv('YAPEPBASE_TEST_MYSQL_RO_USER');
		$roPassword = getenv('YAPEPBASE_TEST_MYSQL_RO_PASSWORD');
		$roDatabase = getenv('YAPEPBASE_TEST_MYSQL_RO_DATABASE');

		parent::setUp();
		$this->config = Config::getInstance();
		$this->config->set(array(
			'application.database.test.rw.backendType'     => 'mysql',
			'application.database.test.rw.host'            => $rwHost,
			'application.database.test.rw.user'            => $rwUser,
			'application.database.test.rw.password'        => $rwPassword,
			'application.database.test.rw.database'        => $rwDatabase,
			'application.database.test.rw.charset'         => 'utf8',

			'application.database.test.ro.backendType'     => 'mysql',
			'application.database.test.ro.host'            => $roHost,
			'application.database.test.ro.user'            => $roUser,
			'application.database.test.ro.password'        => $roPassword,
			'application.database.test.ro.database'        => $roDatabase,
			'application.database.test.ro.charset'         => 'utf8',
		));

		$this->connection = DbFactory::getConnection('test', DbFactory::TYPE_READ_WRITE);

		$this->connection->query('DROP TABLE IF EXISTS test');
		$createTestTable = '
			CREATE TABLE test (
				id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
				`key`       VARCHAR(255)          NOT NULL,
				`value`     VARCHAR(255)          NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY idx_unique_key(`key`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		';

		$this->connection->query($createTestTable);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		if (!$this->isRunnable) {
			return;
		}

		$this->connection->query('DROP TABLE IF EXISTS test');

		if (!empty($this->config)) {
			$this->config->clear();
		}
		$this->config = null;
		DbFactory::clearConnections();
		parent::tearDown();
	}

	/**
	 * Tests a basic process route (insert, selectOne, update, delete)
	 */
	public function testInsertAndSelectOneAndUpdateAndDelete() {
		$table = new TestTableMysqlMock();

		$lastId = true;
		$table->insert(array(
				TestTableMysqlMock::FIELD_KEY   => 'testKey',
				TestTableMysqlMock::FIELD_VALUE => 'testValue'
			), array(), $lastId);

		$row = $table->selectOne(array(TestTableMysqlMock::FIELD_KEY => 'testKey'));
		$this->assertEquals($row[TestTableMysqlMock::FIELD_VALUE], 'testValue');
		$this->assertEquals($row[TestTableMysqlMock::FIELD_ID], $lastId);

		$table->update(array(TestTableMysqlMock::FIELD_VALUE => 'testValue2'),
			array(TestTableMysqlMock::FIELD_ID => $lastId));
		$row = $table->selectOne(array(TestTableMysqlMock::FIELD_ID => $lastId));
		$this->assertEquals($row[TestTableMysqlMock::FIELD_VALUE], 'testValue2');

		$table->delete(array(TestTableMysqlMock::FIELD_ID => $lastId));
		$row = $table->selectOne(array(TestTableMysqlMock::FIELD_ID => $lastId));
		$this->assertEquals($row, false);
	}

	/**
	 * Tests a basic process route (insertMany, select, insert, selectPaged)
	 */
	public function testInsertManyAndSelectAndInsertAndSelectPaged() {
		$table = new TestTableMysqlMock();

		$table->insert(array(
			array(TestTableMysqlMock::FIELD_KEY => 'testKey1', TestTableMysqlMock::FIELD_VALUE => 'testValue1'),
			array(TestTableMysqlMock::FIELD_KEY => 'testKey2', TestTableMysqlMock::FIELD_VALUE => 'testValue2'),
			array(TestTableMysqlMock::FIELD_KEY => 'testKey3', TestTableMysqlMock::FIELD_VALUE => 'testValue1'),
			array(TestTableMysqlMock::FIELD_KEY => 'testKey4', TestTableMysqlMock::FIELD_VALUE => 'testValue2'),
		));

		$rows = $table->select(array(TestTableMysqlMock::FIELD_VALUE => 'testValue1'),
			TestTableMysqlMock::FIELD_KEY, DbTable::ORDER_DESC);

		$expectedResult = array(
			array(TestTableMysqlMock::FIELD_KEY => 'testKey3', TestTableMysqlMock::FIELD_VALUE => 'testValue1'),
			array(TestTableMysqlMock::FIELD_KEY => 'testKey1', TestTableMysqlMock::FIELD_VALUE => 'testValue1'),
		);

		foreach ($rows as $index => $row) {
			$this->assertEquals($expectedResult[$index],
				array(
					TestTableMysqlMock::FIELD_KEY   => $row[TestTableMysqlMock::FIELD_KEY],
					TestTableMysqlMock::FIELD_VALUE => $row[TestTableMysqlMock::FIELD_VALUE],
				)
			);
		}

		$table->insert(
			array(
				TestTableMysqlMock::FIELD_KEY   => 'testKey4',
				TestTableMysqlMock::FIELD_VALUE => 'testValue3'
			),
			array(TestTableMysqlMock::FIELD_VALUE => 'testValue4')
		);

		$itemCount = true;
		$rows = $table->selectPaged(array(), TestTableMysqlMock::FIELD_KEY, DbTable::ORDER_ASC, 1, 2, $itemCount);

		$this->assertEquals(4, $itemCount);
		$rows = $rows + $table->selectPaged(array(), TestTableMysqlMock::FIELD_KEY, DbTable::ORDER_ASC, 2, 2, $itemCount);

		$expectedResult = array(
			array(TestTableMysqlMock::FIELD_KEY => 'testKey1', TestTableMysqlMock::FIELD_VALUE => 'testValue1'),
			array(TestTableMysqlMock::FIELD_KEY => 'testKey2', TestTableMysqlMock::FIELD_VALUE => 'testValue2'),
			array(TestTableMysqlMock::FIELD_KEY => 'testKey3', TestTableMysqlMock::FIELD_VALUE => 'testValue1'),
			array(TestTableMysqlMock::FIELD_KEY => 'testKey4', TestTableMysqlMock::FIELD_VALUE => 'testValue4'),
		);

		foreach ($rows as $index => $row) {
			$this->assertEquals($expectedResult[$index],
				array(
					TestTableMysqlMock::FIELD_KEY   => $row[TestTableMysqlMock::FIELD_KEY],
					TestTableMysqlMock::FIELD_VALUE => $row[TestTableMysqlMock::FIELD_VALUE],
				)
			);
		}
	}
}

