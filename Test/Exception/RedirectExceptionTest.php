<?php

namespace YapepBase\Exception;

use \YapepBase\Exception\RedirectException;

/**
 * Test class for RedirectException.
 * Generated by PHPUnit on 2011-12-05 at 11:48:37.
 */
class RedirectExceptionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers \YapepBase\Exception\RedirectException
     */
    public function testTarget() {
        $e = new RedirectException('http://www.example.com/', RedirectException::TYPE_EXTERNAL);
        $this->assertEquals('http://www.example.com/', $e->getTarget());
        $this->assertEquals(RedirectException::TYPE_EXTERNAL, $e->getCode());
    }
}
