<?php

namespace YapepBase\Controller;

class BaseControllerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var MockController
     */
    protected $object;

    public function testRun() {
        $request = new \YapepBase\Test\Mock\Request\RequestMock('http://example.com/');
        $output = new \YapepBase\Test\Mock\Response\OutputMock();
        $response = new \YapepBase\Response\HttpResponse($output);
        $this->object = new \YapepBase\Test\Mock\Controller\MockController($request, $response);
        $this->assertFalse($this->object->ran);
        $this->object->run('test');
        $this->assertTrue($this->object->ran);

        try {
            $this->object->run('nonExistent');
            $this->fail('Running a non-existent action should result in a ControllerException');
        } catch (\YapepBase\Exception\ControllerException $e) {
            $this->assertEquals(\YapepBase\Exception\ControllerException::ERR_ACTION_NOT_FOUND, $e->getCode());
        }

        try {
            $this->object->run('error');
            $this->fail('Running an action with an invalid result in a ControllerException');
        } catch (\YapepBase\Exception\ControllerException $e) {
            $this->assertEquals(\YapepBase\Exception\ControllerException::ERR_INVALID_ACTION_RESULT, $e->getCode());
        }

        $this->object->run('returnString');
        $this->assertEquals('test string', $response->getRenderedBody());

        $this->object->run('returnView');
        $this->assertEquals('view test string', $response->getRenderedBody());

        \YapepBase\Application::getInstance()->getDiContainer()->addControllerSearchNamespace('\YapepBase\Test\Mock\Controller');
        $this->object->run('redirect');
        $this->assertEquals('redirect test', $response->getRenderedBody());
    }
}
