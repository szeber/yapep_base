<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Event
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Event;

/**
 * Class that describes an event.
 *
 * @package    YapepBase
 * @subpackage Event
 */
class Event {

	/** Application start event type. */
	const TYPE_APPSTART = 'application.start';
	/** Application finish event tyoe. */
	const TYPE_APPFINISH = 'application.finish';
	/** Controller before action event type. */
	const TYPE_CONTROLLER_BEFORE = 'controller.beforeAction';
	/** Controller after action event type. */
	const TYPE_CONTROLLER_AFTER = 'controller.afterAction';

	/**
	 * The event's type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Stores the data specific to the event in an associative array.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Constructor
	 *
	 * @param string $type   The event type. {@uses self::TYPE_*}
	 * @param array  $data   The event data.
	 */
	public function __construct($type, array $data = array()) {
		$this->type = $type;
		$this->data = $data;
	}

	/**
	 * Returns the event type.
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns the data for the event.
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

}