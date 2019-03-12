<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit;

use PHPUnit\Framework\TestCase;
use YapepBase\Application;
use YapepBase\DependencyInjection\Container;
use YapepBase\DependencyInjection\PimpleContainer;
use Mockery;
use YapepBase\Helper\TextHelper;

abstract class TestAbstract extends TestCase
{
    /** @var PimpleContainer */
    protected $pimpleContainer;
    /** @var Container */
    protected $diContainer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetApplicationInstance();
        $this->initDi();
    }

    protected function tearDown(): void
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
        Mockery::close();
        parent::tearDown();
    }

    protected function resetApplicationInstance()
    {
        $reflection = new \ReflectionClass(Application::getInstance());

        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        $instance->setAccessible(false);
    }

    protected function initDi()
    {
        $this->pimpleContainer = new PimpleContainer();
        $this->diContainer     = new Container($this->pimpleContainer);
        Application::getInstance()->setDiContainer($this->diContainer);
    }

    protected function assertSameHtmlStructure(string $expectedHtml, string $actualHtml)
    {
        $textHelper   = new TextHelper();
        $expectedHtml = $textHelper->stripWhitespaceDuplicates($expectedHtml);
        $actualHtml   = $textHelper->stripWhitespaceDuplicates($actualHtml);

        $this->assertSame($expectedHtml, $actualHtml);
    }
}
