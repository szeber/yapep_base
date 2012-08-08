<?php

namespace YapepBase\Database;


use YapepBase\Database\DbFactory;
use YapepBase\Database\MysqlConnection;
use YapepBase\Exception\DatabaseException;

/**
 * MysqlConnection test case.
 */
class MysqlConnectionTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var \YapepBase\Database\MysqlConnection
	 */
	private $connection;

	/**
	 * @var bool   Is the test runnable.
	 */
	protected $isRunnable = true;

	/**
	 * Cosntructor
	 */
	public function __construct() {
		$rwHost = getenv('YAPEPBASE_TEST_MYSQL_RW_HOST');

		// TODO: You have to configure the previous ENV vairables on order to make this test work [emul]
		if (empty($rwHost)) {
			$this->isRunnable = false;
		}
		parent::__construct();
	}

	/**
	 * Prepares the environment before running a test.
	 *
	 * We are using '_' as the param prefix.
	 */
	protected function setUp() {

		if (!$this->isRunnable) {
			$this->markTestSkipped('Required ENV variables missing');
			return;
		}

		$this->connection = $this->getConnection();

		$this->connection->query('DROP TABLE IF EXISTS test');
		$this->connection->query('DROP TABLE IF EXISTS test2');
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
		parent::setUp();
	}

	/**
	 * Creates a new MysqlConnection
	 *
	 * @param array $extraOptions   An array containing any extra configuration options.
	 *
	 * @return \YapepBase\Database\MysqlConnection
	 */
	protected function getConnection(array $extraOptions = array()) {
		$rwHost     = getenv('YAPEPBASE_TEST_MYSQL_RW_HOST');
		$rwUser     = getenv('YAPEPBASE_TEST_MYSQL_RW_USER');
		$rwPassword = getenv('YAPEPBASE_TEST_MYSQL_RW_PASSWORD');
		$rwDatabase = getenv('YAPEPBASE_TEST_MYSQL_RW_DATABASE');
		$rwPort     = (int)getenv('YAPEPBASE_TEST_MYSQL_RW_PORT');
		$rwPort     = (empty($rwPort) ? 3306 : $rwPort);

		$config = array(
			'host'            => $rwHost,
			'user'            => $rwUser,
			'password'        => $rwPassword,
			'database'        => $rwDatabase,
			'charset'         => 'utf8',
			'port'            => $rwPort,
		);

		return new MysqlConnection(array_merge($config, $extraOptions), 'test', '_');
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		if (!$this->isRunnable) {
			return;
		}

		$this->connection->query('DROP TABLE IF EXISTS test');
		$this->connection->query('DROP TABLE IF EXISTS test2');

		parent::tearDown();
	}

	/**
	 * Tests running queries without using bound params
	 */
	public function testQuery() {
		$sql = '
			INSERT INTO
				test
				(`key`, `value`)
			VALUES
				(\'key1\', \'value1\')
		';

		$this->connection->query($sql);

		$sql = '
			SELECT
				`key`
			FROM
				test
			WHERE
				id = 1
		';

		$result = $this->connection->query($sql);

		$this->assertInstanceOf('\YapepBase\Database\DbResult', $result, 'Result is of wrong type');
		$this->assertEquals('key1', $result->fetchColumn(0), 'Invalid result');
	}

	/**
	 * Tests running queries using bound params.
	 */
	public function testBoundQuery() {
		$sql = '
			INSERT INTO
				test
				(`key`, `value`)
			VALUES
				(:_key, :_value)
		';

		$params = array(
			'key'   => 'key1',
			'value' => 'value1',
		);

		$this->connection->query($sql, $params);

		$sql = '
			SELECT
				`key`
			FROM
				test
			WHERE
				id = :_id
		';

		$params = array(
			'id' => 1,
		);

		$result = $this->connection->query($sql, $params);

		$this->assertInstanceOf('\YapepBase\Database\DbResult', $result, 'Result is of wrong type');
		$this->assertEquals('key1', $result->fetchColumn(0), 'Invalid result');
	}

	/**
	 * Tests a successful transaction
	 */
	public function testSuccessfulTransaction() {
		$sql = '
			INSERT INTO
				test
				(`key`, `value`)
			VALUES
				(:_key, :_value)
		';

		$this->connection->beginTransaction();

		$this->connection->query($sql, array('key' => 'key1', 'value' => 'value1'));
		$this->connection->query($sql, array('key' => 'key2', 'value' => 'value2'));

		$this->assertTrue($this->connection->completeTransaction(), 'Transaction failed');

		$sql = '
			SELECT
				`key`, `value`
			FROM
				test
			ORDER BY
				id ASC
		';

		$result = $this->connection->query($sql);

		$row = $result->fetch();
		$this->assertEquals('key1', $row['key'], 'Invalid first ID');
		$this->assertEquals('value1', $row['value'], 'Invalid first ID');

		$row = $result->fetch();
		$this->assertEquals('key2', $row['key'], 'Invalid second ID');
		$this->assertEquals('value2', $row['value'], 'Invalid second ID');

		$this->assertFalse($result->fetch());
	}

	/**
	 * Tests a manualy failed transaction
	 */
	public function testFailedTransaction() {
		$sql = '
			INSERT INTO
				test
				(`key`, `value`)
			VALUES
				(:_key, :_value)
		';

		$this->connection->beginTransaction();

		$this->connection->query($sql, array('key' => 'key1', 'value' => 'value1'));
		$this->connection->failTransaction();
		$this->connection->query($sql, array('key' => 'key2', 'value' => 'value2'));

		$this->assertFalse($this->connection->completeTransaction(), 'Transaction failed');

		$sql = '
			SELECT
				`key`, `value`
			FROM
				test
			ORDER BY
				id ASC
		';

		$result = $this->connection->query($sql);

		$this->assertFalse($result->fetch());
	}

	/**
	 * Tests a transaction with an error
	 */
	public function testErrorTransaction() {
		$sql = '
			INSERT INTO
				test
				(`key`, `value`)
			VALUES
				(:_key, :_value)
		';

		$this->connection->beginTransaction();

		$this->connection->query($sql, array('key' => 'key1', 'value' => 'value1'));
		$sql = '
			INSERT INTO
				test
				(`key`, `value2`)
			VALUES
				(:_key, :_value)
		';

		try {
			$this->connection->query($sql, array('key' => 'key2', 'value' => 'value2'));
		} catch (DatabaseException $e) {
		}

		$this->assertFalse($this->connection->completeTransaction(), 'Transaction failed');

		$sql = '
			SELECT
				`key`, `value`
			FROM
				test
			ORDER BY
				id ASC
		';

		$result = $this->connection->query($sql);

		$this->assertFalse($result->fetch());
	}

	/**
	 * Tests if a query error produces an exception
	 */
	public function testQueryError() {
		$this->setExpectedException('\YapepBase\Exception\DatabaseException');

		$sql = '
			SLECT
				*
			FROM
				test
		';

		$this->connection->query($sql);
	}

	/**
	 * Tests the quoting
	 */
	public function testQuote() {
		$this->assertEquals('\'test\'', $this->connection->quote('test'), 'Error quoting simple string');
		$this->assertEquals('\'o\\\'neill\'', $this->connection->quote('o\'neill'),
			'Error quoting string with quote mark');
	}

	/**
	 * Tests connections with several connection options
	 *
	 * @return void
	 */
	public function testConnectionOptions() {
		$connection = $this->getConnection(array('useTraditionalStrictMode' => true));
		$this->assertNotEmpty($connection->query('SELECT @@SESSION.sql_mode')->fetchColumn(),
			'The session sql mode is empty on the traditional mode connection');

		$connection = $this->getConnection(array('timezone' => 'UTC'));
		$result = $connection->query('SELECT @@SESSION.time_zone')->fetchColumn();
		if ('SYSTEM' === $result) {
			// If the system's timezone is UTC, SYSTEM will get returned, so test with another zone. If it changes to
			// that, the test should be considered successful.
			$connection = $this->getConnection(array('timezone' => '+12:00'));
			$result = $connection->query('SELECT @@SESSION.time_zone')->fetchColumn();
			$this->assertEquals('+12:00', $result, 'The time zone has not been set');
		} else {
			$this->assertEquals('UTC', $result, 'The time zone has not been set');
		}
	}

}
