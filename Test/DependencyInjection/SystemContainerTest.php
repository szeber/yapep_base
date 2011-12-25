<?php

namespace YapepBase\Test\DependencyInjection;

class SystemContainerTest extends \PHPUnit_Framework_TestCase {
    public function testConstructor() {
        $sc = new \YapepBase\DependencyInjection\SystemContainer();
        $this->assertInstanceOf('\YapepBase\ErrorHandler\ErrorHandlerRegistry', $sc->getErrorHandlerRegistry());
        $this->assertInstanceOf('\YapepBase\Log\Message\ErrorMessage', $sc->getErrorLogMessage());
        $this->assertInstanceOf('\YapepBase\Event\EventHandlerRegistry', $sc->getEventHandlerRegistry());
        $this->assertInstanceOf('\Memcache', $sc->getMemcache());
        $this->assertInstanceOf('\Memcached', $sc->getMemcached());
    }
    public function testGetController() {
        $sc = new \YapepBase\DependencyInjection\SystemContainer();
        $sc->setControllerSearchNamespaces(array());
        try {
            $request = new \YapepBase\Test\Mock\Request\RequestMock('', '');
            $response = new \YapepBase\Test\Mock\Response\ResponseMock();
            $sc->getController('Mock', $request, $response);
            $this->fail('Getting a controller with an empty search array should result in a ControllerException');
        } catch (\YapepBase\Exception\ControllerException $e) {
            $this->assertEquals(\YapepBase\Exception\ControllerException::ERR_CONTROLLER_NOT_FOUND, $e->getCode());
        }
        $sc->addControllerSearchNamespace('\YapepBase\Test\Mock\Controller');
        $this->assertInstanceOf('\YapepBase\Controller\BaseController', $sc->getController('Mock', $request, $response));
    }
    public function testGetBlock() {
        $sc = new \YapepBase\DependencyInjection\SystemContainer();
        $sc->setBlockSearchNamespaces(array());
        try {
            $sc->getBlock('Mock');
            $this->fail('Getting a controller with an empty search array should result in a ViewException');
        } catch (\YapepBase\Exception\ViewException $e) {
            $this->assertEquals(\YapepBase\Exception\ViewException::ERR_BLOCK_NOT_FOUND, $e->getCode());
        }
        $sc->addBlockSearchNamespace('\YapepBase\Test\Mock\View');
        $this->assertInstanceOf('\YapepBase\View\Block', $sc->getBlock('Mock'));
    }
}
