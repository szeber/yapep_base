<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Response\Entity;

abstract class TestAbstract extends \YapepBase\Test\Unit\TestAbstract
{

    protected function getHeadersAfterMethodCall(callable $testedMethod): array
    {
        ob_start();

        $testedMethod();

        $headers = xdebug_get_headers();
        ob_get_clean();

        return $headers;
    }
}
