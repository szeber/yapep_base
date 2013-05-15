<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Event
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

	/** Event type that is raised when the application starts to run. */
	const TYPE_APPLICATION_BEFORE_RUN = 'application.beforeRun';
	/**
	 * Event type that is raised when the application finishes to run.
	 *
	 * This should be the last event raised by the application.
	 */
	const TYPE_APPLICATION_AFTER_RUN = 'application.afterRun';
	/** Event type that is raised before the controller's run method is called. */
	const TYPE_APPLICATION_BEFORE_CONTROLLER_RUN = 'application.beforeControllerRun';
	/** Event type that is raised after the controller's run method finishes. */
	const TYPE_APPLICATION_AFTER_CONTROLLER_RUN = 'application.afterControllerRun';
	/** Event that's sent after the controller finishes and before the output is sent. */
	const TYPE_APPLICATION_BEFORE_OUTPUT_SEND = 'application.beforeOutputSend';
	/** Event that's sent after the controller finishes and the output is sent. */
	const TYPE_APPLICATION_AFTER_OUTPUT_SEND = 'application.afterOutputSend';
	/** Event type that is raised before the controller's action is called (after the before() method). */
	const TYPE_CONTROLLER_BEFORE_ACTION = 'controller.beforeAction';
	/** Event type that is raised after the controller's action is called (before the after() method). */
	const TYPE_CONTROLLER_AFTER_ACTION = 'controller.afterAction';

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