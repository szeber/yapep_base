<?php

namespace YapepBase\Test\Mock\Controller;

class HttpMockController extends \YapepBase\Controller\HttpController {
    public function testRedirect() {
        $this->redirectToUrl('http://www.example.com/', 301);
    }
}