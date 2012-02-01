<?php

namespace YapepBase\Test\Mock\Util;

class CollectionElementMock {
    protected $id;
    function __construct() {
        $this->id = uniqid('', true);
    }
}