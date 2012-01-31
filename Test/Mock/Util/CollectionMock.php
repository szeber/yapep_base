<?php

namespace YapepBase\Test\Mock\Util;

class CollectionMock extends \YapepBase\Util\Collection {
    function typeCheck($element) {
        if (!$element instanceof \YapepBase\Test\Mock\Util\CollectionElementMock) {
            throw new \YapepBase\Exception\TypeException($element,
                '\\YapepBase\\Test\\Mock\\Util\\CollectionElementMock');
        }
    }
}