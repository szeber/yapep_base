<?php

namespace YapepBase\Test\Mock\Util;

/**
 * Mock class for CollectionElement
 *
 * @codeCoverageIgnore
 */
class CollectionElementMock {
	protected $id;
	function __construct() {
		$this->id = uniqid('', true);
	}
}