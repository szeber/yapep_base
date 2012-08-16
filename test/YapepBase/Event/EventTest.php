<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Event
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Event;

/**
 * EventTest class
 *
 * @package    YapepBase
 * @subpackage Test\Event
 */
use YapepBase\Event\Event;

class EventTest extends \PHPUnit_Framework_TestCase {

	public function testEvent() {
		$event = new Event('test');
		$this->assertSame('test', $event->getType(), 'Event does not return the type that was set');
		$this->assertSame(array(), $event->getData(),
			'Not an empty array is returned for the data when data was not set.');

		$testArray = array('key' => 'value');
		$event = new Event('test', $testArray);
		$this->assertSame('test', $event->getType(), 'Event does not return the type that was set');
		$this->assertSame($testArray, $event->getData(),
			'Not the set array is returned for the data.');
	}

}