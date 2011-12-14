<?php

namespace YapepBase\Test;

class UtilityFunctionsTest extends \PHPUnit_Framework_TestCase {
    public function testRecursiveStripSlashes() {
        $source = array(
            'var1' => 'test\\\"var',
            'var2' => array(
                'var2-1' => 'test\\\\var',
                'var2-2' => '\\\'',
            ),
        );
        $target = array(
            'var1' => 'test\"var',
            'var2' => array(
                'var2-1' => 'test\\var',
                'var2-2' => '\'',
            ),
        );
        $this->assertEquals($target, \YapepBase\UtilityFunctions::recursiveStripSlashes($source));
    }
}
