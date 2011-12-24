<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Database
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Database;
use \PDOStatement;
use \Iterator;

/**
 * DbResult class
 *
 * @package    YapepBase
 * @subpackage Database
 */
class DbResult implements Iterator {

    /**
     * Stores the PDOStatement instance.
     *
     * @var \PDOStatement
     */
    protected $statement = null;

    /**
     * Stores the current row.
     *
     * @var array
     */
    protected $row;

    /**
     * Stores the current row index.
     *
     * @var int
     */
    protected $rowIndex = -1;

    /**
     * Constructor.
     *
     * @param \PDOStatement $statement
     */
    public function __construct(PDOStatement $statement) {
        $this->statement = $statement;
    }

    /**
     * Returns the current row.
     *
     * @return array
     */
    public function current() {
        if (is_null($this->row)) {
            return $this->next();
        }
        return $this->row;
    }

    /**
     * Returns the current index of the result.
     *
     * @return int
     */
    public function key() {
        if (-1 === $this->rowIndex) {
            $this->next();
        }
        return $this->rowIndex;
    }

    /**
     * Returns the next row from the result and increments the row counter
     *
     * @return array|bool
     */
    public function next() {
        $this->row = $this->statement->fetch(\PDO::FETCH_ASSOC);
        if (false !== $this->row) {
            $this->rowIndex++;
        }
        return $this->row;
    }

    /**
     * Returns TRUE if the current row is in the resultset, FALSE at the end.
     *
     * @return bool
     */
    public function valid() {
        return ($this->current() !== false);
    }

    /**
     * Does nothing, since the PDO result can only be traversed once.
     *
     * @return void
     */
    public function rewind() {}

    /**
     * Returns one row from the resultset, or FALSE if there are no more rows.
     *
     * @return array|bool
     */
    public function fetch() {
        return $this->next();
    }

    /**
     * Returns all rows from the resultset.
     *
     * @return array
     */
    public function fetchAll() {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns a column from the resultset.
     *
     * @param int $columnNumber   The number of the column in the row (zero indexed).
     *
     * @return mixed
     */
    public function fetchColumn($columnNumber = 0) {
        return $this->statement->fetchColumn($columnNumber);
    }
}