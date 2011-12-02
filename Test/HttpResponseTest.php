<?php

namespace YapepBase\Test;

use YapepBase\Response\HttpResponse;
use YapepBase\Config;

require_once dirname(__FILE__) . '/../bootstrap.php';

class HttpResponseTest extends \PHPUnit_Framework_TestCase {
	/**
	 * The request
	 *
	 * @var \YapepBase\Request\HttpResponse
	 */
	protected $response;
        
    protected function setUp() {

    parent::setUp();

            $this->response = new HttpResponse();
    }

    /**
     * This function tests, that bodies are 
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
}