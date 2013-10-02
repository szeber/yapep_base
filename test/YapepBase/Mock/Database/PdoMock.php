<?php

namespace YapepBase\Mock\Database;


/**
 * Mock class for PDO
 *
 * @codeCoverageIgnore
 */
class PdoMock extends \PDO {

	/**
	 * Stores how many times the execute method has been called.
	 *
	 * @var int
	 */
	protected $executeCalled = 0;

	/**
	 * Stores the behavior of the execute method.
	 *
	 * @var array
	 */
	protected $executeBehavior = array();

	/**
	 * Sets the behavior of the execute method.
	 *
	 * @param array $behavior   The behavior where the key is the count of executions, and the value is the return value
	 *                          If the value is an Exception it will be thrown.
	 *
	 * @return void
	 */
	public function __construct(array $behavior) {
		$this->executeBehavior = $behavior;
	}

	/**
	 * Prepare
	 *
	 * @param string $statement
	 * @param array  $driver_options
	 *
	 * @return PDOStatementMock
	 */
	public function prepare($statement, array $driver_options = null) {
		$this->executeCalled++;

		if (!empty($this->executeBehavior[$this->executeCalled])) {
			if ($this->executeBehavior[$this->executeCalled] instanceof \Exception) {
				$statement =  new PDOStatementMock(null);
				$statement->setException($this->executeBehavior[$this->executeCalled]);
			}
			else {
				$statement = new PDOStatementMock($this->executeBehavior[$this->executeCalled]);
			}
		}
		else {
			$statement = new PDOStatementMock(null);
		}

		return $statement;
	}
}