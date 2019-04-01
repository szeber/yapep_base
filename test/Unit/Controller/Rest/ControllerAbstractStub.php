<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Controller\Rest;

use YapepBase\Controller\Rest\ControllerAbstract;
use YapepBase\Controller\Rest\Exception\Exception;
use YapepBase\View\Data\SimpleData;
use YapepBase\View\Template\JsonTemplate;

class ControllerAbstractStub extends ControllerAbstract
{
    /** @var Exception */
    public $exception;

    public function getTest(): JsonTemplate
    {
        $viewData = new SimpleData(['get' => 1]);

        return new JsonTemplate($viewData);
    }

    public function postTest(): JsonTemplate
    {
        $viewData = new SimpleData(['post' => 1]);

        return new JsonTemplate($viewData);
    }

    public function putTest(): JsonTemplate
    {
        $viewData = new SimpleData(['post' => 1]);

        return new JsonTemplate($viewData);
    }

    public function deleteTest(): JsonTemplate
    {
        $viewData = new SimpleData(['delete' => 1]);

        return new JsonTemplate($viewData);
    }

    public function getException()
    {
        throw $this->exception;
    }
}
