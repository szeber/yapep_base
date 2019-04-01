<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Controller;

use YapepBase\Controller\ControllerAbstract;
use YapepBase\View\IRenderable;

class ControllerAbstractStub extends ControllerAbstract
{
    /** @var string  */
    public $resultString = 'string';
    /** @var IRenderable */
    public $resultView;
    /** @var bool  */
    public $isRedirected = false;

    public function __construct()
    {
        $this->resultView = \Mockery::mock(IRenderable::class);
    }

    public function doTestWithStringResult(): string
    {
        return $this->resultString;
    }

    public function doTestWithRenderableResult(): IRenderable
    {
        return $this->resultView;
    }

    public function doTestInvalidResult(): \stdClass
    {
        return new \stdClass();
    }

    public function doTestRedirectedTo(): void
    {
        $this->isRedirected = true;
    }

    public function internalRedirect(string $controllerClassName, string $action): void
    {
        parent::internalRedirect($controllerClassName, $action);
    }
}
