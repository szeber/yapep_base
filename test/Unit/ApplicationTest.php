<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit;


use YapepBase\Application;
use YapepBase\DependencyInjection\Container;
use Mockery;
use Mockery\MockInterface;
use YapepBase\DependencyInjection\IContainer;
use YapepBase\ErrorHandler\ErrorHandlerRegistry;

class ApplicationTest extends TestAbstract
{
    /** @var Application */
    protected $application;
    /** @var MockInterface */
    protected $errorHandlerRegistry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetApplicationInstance();
    }

    protected function resetApplicationInstance()
    {
        $reflection = new \ReflectionClass(Application::getInstance());

        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        $instance->setAccessible(false);

        $this->application = Application::getInstance();
    }

    public function testGetDiContainerWhenNotSetBefore_shouldReturnNewContainer()
    {
        $result = $this->application->getDiContainer();

        $this->assertInstanceOf(Container::class, $result);
    }

    public function testGetDiContainerWhenSetBefore_shouldReturnSetContainer()
    {
        $container = Mockery::mock(IContainer::class);

        $this->application->setDiContainer($container);
        $result = $this->application->getDiContainer();

        $this->assertSame($container, $result);
    }

    protected function expectErrorHandlerRegistered()
    {
        $this->errorHandlerRegistry = Mockery
            ::mock(ErrorHandlerRegistry::class)
            ->shouldReceive('register')
            ->once()
            ->getMock();
    }
}
