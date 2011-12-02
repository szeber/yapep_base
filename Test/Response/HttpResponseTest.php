<?php

namespace YapepBase\Test\Response;

use YapepBase\Response\HttpResponse;
use YapepBase\Config;

require_once dirname(__FILE__) . '/../../bootstrap.php';

class HttpResponseTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \YapepBase\Test\Mock\Response\OutputMock
     */
    protected $output;
    
	/**
	 * The request
	 *
	 * @var \YapepBase\Response\HttpResponse
	 */
	protected $response;

	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
    protected function setUp() {

        parent::setUp();

        $this->output = new \YapepBase\Test\Mock\Response\OutputMock();
        $this->createCleanResponse();
    }
    
    /**
     * This function creates a clean HttpResponse instance.
     */
    protected function createCleanResponse() {
        $this->output->clean();
        $this->response = new HttpResponse($this->output);        
    }

    /**
     * This function tests, that bodies are correctly stored and rendered
     */
    public function testBodyStoringAndRendering() {

        Config::getInstance()->clear();
        Config::getInstance()->set('system.response.gzip', false);

        $this->response->setRenderedBody('test body 1');
        $this->assertEquals('test body 1', $this->response->getRenderedBody(), 'HttpResponse does not correctly store or render a plain text body.');

        $view = new \YapepBase\Test\Mock\Response\ViewMock();
        $view->set('test body 2');
        $this->response->setBody($view);
        $this->assertEquals('test body 2', $this->response->getRenderedBody(), 'HttpResponse does not correctly store or render an IView body.');
    }
    
    /**
     * Tests, if a response can be sent twice.
     */
    public function testResponseSend() {
        //$this->markTestIncomplete('This test is still buggy, because a headers already sent error is thrown.');
        
        ob_start();
        $this->response->send();
        try {
            $this->response->send();
            $this->fail('A response can be sent twice');
        } catch (\YapepBase\Exception\Exception $e) { }
        ob_end_clean();
    }
    
    /**
     * @covers \YapepBase\Response\HttpResponse::checkStandards
     */
    public function testCheckStandards() {
        /**
         * Check HTTP 204
         */
        try {
            $this->createCleanResponse();
            $this->response->setStatusCode(204);
            $this->response->setRenderedBody('test');
            $this->response->send();
            $this->fail('Standards compliancy test should fail with status code 204 and non-empty body.');
        } catch (\YapepBase\Exception\StandardsComplianceException $e) {}
        $this->createCleanResponse();
        $this->response->setStatusCode(204);
        $this->response->setRenderedBody('');
        $this->response->send();
        
        /**
         * Check HTTP 206
         */
        try {
            $this->createCleanResponse();
            $this->response->setStatusCode(206);
            $this->response->send();
            $this->fail('Standards compliancy test should fail with status code 206 and missing Date and Content-Range headers.');
        } catch (\YapepBase\Exception\StandardsComplianceException $e) {}
        try {
            $this->createCleanResponse();
            $this->response->setStatusCode(206);
            $this->response->setHeader('Date', date('r'));
            $this->response->send();
            $this->fail('Standards compliancy test should fail with status code 206 and missing Content-Range headers.');
        } catch (\YapepBase\Exception\StandardsComplianceException $e) {}
        try {
            $this->createCleanResponse();
            $this->response->setStatusCode(206);
            $this->response->setHeader('Content-Range', 'bytes 21010-47021/47022');
            $this->response->send();
            $this->fail('Standards compliancy test should fail with status code 206 and missing Date headers.');
        } catch (\YapepBase\Exception\StandardsComplianceException $e) {}
        $this->createCleanResponse();
        $this->response->setStatusCode(206);
        $this->response->setHeader('Date', date('r'));
        $this->response->setHeader('Content-Range', 'bytes 21010-47021/47022');
        $this->response->send();
        
        /**
         * Check HTTP 301, 302, 303 and 307
         */
        foreach (array(301, 302, 303, 307) as $statuscode) {
            try {
                $this->createCleanResponse();
                $this->response->setStatusCode($statuscode);
                $this->response->send();
                $this->fail('Standards compliancy test should fail with status code ' . $statuscode . ' and no Location header.');
            } catch (\YapepBase\Exception\StandardsComplianceException $e) {}
            $this->createCleanResponse();
            $this->response->setStatusCode($statuscode);
            $this->response->setHeader('Location', 'http://example.com/');
            $this->response->send();
        }
        
        /**
         * Check HTTP 304
         */
        try {
            $this->createCleanResponse();
            $this->response->setStatusCode(304);
            $this->response->send();
            $this->fail('Standards compliancy test should fail with status code 304 and missing Date header.');
        } catch (\YapepBase\Exception\StandardsComplianceException $e) {}
        $this->createCleanResponse();
        $this->response->setStatusCode(304);
        $this->response->setHeader('Date', date('r'));
        $this->response->send();

        /**
         * Check HTTP 401
         */
        try {
            $this->createCleanResponse();
            $this->response->setStatusCode(401);
            $this->response->send();
            $this->fail('Standards compliancy test should fail with status code 401 and missing WWW-Authenticate header.');
        } catch (\YapepBase\Exception\StandardsComplianceException $e) {}
        $this->createCleanResponse();
        $this->response->setStatusCode(401);
        $this->response->setHeader('WWW-Authenticate', 'abc123');
        $this->response->send();
        
        /**
         * Check HTTP 405
         */
        try {
            $this->createCleanResponse();
            $this->response->setStatusCode(405);
            $this->response->send();
            $this->fail('Standards compliancy test should fail with status code 405 and missing Allow header.');
        } catch (\YapepBase\Exception\StandardsComplianceException $e) {}
        $this->createCleanResponse();
        $this->response->setStatusCode(405);
        $this->response->setHeader('Allow', 'GET, HEAD, PUT');
        $this->response->send();
    }
}