<?php

namespace YapepBase\Test\Mock\Response;

class ResponseMock implements \YapepBase\Response\IResponse {
    public function __construct(\YapepBase\Response\IOutput $output = null) {

    }
    public function setBody(\YapepBase\View\IView $body) {

    }
    public function setRenderedBody($body) {

    }
    public function send() {

    }
    public function sendError() {

    }
}