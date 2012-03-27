<?php

namespace YapepBase\Test\Mock\Controller;

/**
 * @codeCoverageIgnore
 */
class RestMockController extends \YapepBase\Controller\RestController {
	function getXml() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::XML, 'UTF-8');
		return array('test1' => 'test');
	}
	function getJson() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::JSON, 'UTF-8');
		return array('test1' => 'test');
	}
	function getUnknown() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::HTML, 'UTF-8');
		return array('test1' => 'test');
	}
	function getString() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::PLAINTEXT, 'UTF-8');
		return 'test';
	}
	function getInvalid() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::JSON, 'UTF-8');
		return new \stdClass();
	}
}