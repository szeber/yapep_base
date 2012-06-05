<?php

namespace YapepBase\Mock\Util;

/**
 * Mock class for \YapepBase\Util\Collection
 *
 * @codeCoverageIgnore
 */
class CollectionMock extends \YapepBase\Util\Collection {
	function typeCheck($element) {
		if (!$element instanceof \YapepBase\Mock\Util\CollectionElementMock) {
			throw new \YapepBase\Exception\TypeException($element,
				'\\YapepBase\\Test\\Mock\\Util\\CollectionElementMock');
		}
	}
}