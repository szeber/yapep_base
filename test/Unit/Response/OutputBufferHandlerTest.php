<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Response;

use YapepBase\Response\OutputBufferHandler;
use YapepBase\Test\Unit\TestAbstract;

class OutputBufferHandlerTest extends TestAbstract
{
    /** @var OutputBufferHandler */
    protected $outputBufferHandler;
    /** @var int */
    protected $originalObLevel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalObLevel     = ob_get_level();
        $this->outputBufferHandler = new OutputBufferHandler();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        while (ob_get_level() > $this->originalObLevel) {
            ob_get_clean();
        }
    }

    public function testStart_shouldStartOb()
    {
        $this->outputBufferHandler->start();

        $this->assertTrue($this->outputBufferHandler->isStarted());
        $this->assertSame($this->originalObLevel + 1, ob_get_level());
    }

    public function testStartWhenAlreadyStarted_shouldDoNothing()
    {
        $this->outputBufferHandler->start();

        $currentObLevel = ob_get_level();
        $this->outputBufferHandler->start();

        $this->assertSame($currentObLevel, ob_get_level());
    }

    public function testStopWhenNotStarted_shouldDoNothing()
    {
        $result = $this->outputBufferHandler->stop();

        $this->assertSame($this->originalObLevel, ob_get_level());
        $this->assertEmpty($result);
    }

    public function testStopWhenStarted_shouldStopStartedObAndReturnContent()
    {
        $content = 'test';
        $this->outputBufferHandler->start();
        echo $content;
        $result = $this->outputBufferHandler->stop();

        $this->assertSame($this->originalObLevel, ob_get_level());
        $this->assertSame($content, $result);
    }

    public function testIsStartedWhenNotStarted_shouldReturnFalse()
    {
        $isStarted = $this->outputBufferHandler->isStarted();

        $this->assertFalse($isStarted);
    }

    public function testIsStartedWhenStarted_shouldReturnTrue()
    {
        $this->outputBufferHandler->start();
        $isStarted = $this->outputBufferHandler->isStarted();

        $this->assertTrue($isStarted);
    }
}
