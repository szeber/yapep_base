<?php

namespace YapepBase\Test\Mock\Controller;

class HttpMockController extends \YapepBase\Controller\HttpController {
    public function testRedirect() {
        $this->redirectToUrl('http://www.example.com/', 301);
    }

    public function testRedirectToRoute() {
        $this->redirectToRoute('Test', 'test', array(), array('test' => 'test', 'test2' => array('test1', 'test2')), 'test');
    }
}