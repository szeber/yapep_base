<?php

namespace YapepBase\Test\Mock\Database;

class PDOStatementMock extends \PDOStatement {
    protected $data;
    public function __construct($data) {
        $this->data = $data;
    }

    public function fetch($type) {
        switch ($type) {
            case \PDO::FETCH_ASSOC:
                $result = current($this->data);
                next($this->data);
                return $result;
            default:
                throw new \YapepBase\Exception\NotImplementedException();
        }
    }

    public function fetchColumn($column) {
        $row = array_values(current($this->data));
        next($this->data);
        return $row[$column];
    }

    public function fetchAll($type) {
        switch ($type) {
            case \PDO::FETCH_ASSOC:
                return $this->data;
            default:
                throw new \YapepBase\Exception\NotImplementedException();
        }
    }
}