<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Batch
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Batch;
use YapepBase\Application;
use YapepBase\Event\Event;
use YapepBase\Mime\MimeType;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\View\ViewDo;

/**
 * Base class for batch scripts.
 *
 * Handles envent dispatching for application start and finish, and handles unhandled exceptions.
 *
 * @package    YapepBase
 * @subpackage Batch
 */
abstract class BatchScript {

	/**
	 * Stores the content type used by the script for output.
	 *
	 * @var string
	 */
	protected $contentType = MimeType::PLAINTEXT;

	/**
	 * Helper method to execute the script
	 */
	public static function run() {
		$application = Application::getInstance();
		$eventHandlerRegistry = $application->getDiContainer()->getEventHandlerRegistry();
		try {
			$container = Application::getInstance()->getDiContainer();
			$container[SystemContainer::KEY_VIEW_DO] = $container->share(function($container) {
				return new ViewDo(MimeType::PLAINTEXT);
			});

			$eventHandlerRegistry->raise(new Event(Event::TYPE_APPSTART));
			$instance = new static();
			$instance->runScript();
		} catch (\Exception $exception) {
			Application::getInstance()->getErrorHandlerRegistry()->handleException($exception);
		}
		$eventHandlerRegistry->raise(new Event(Event::TYPE_APPFINISH));
	}

	/**
	 * Starts script execution.
	 */
	protected function runScript() {
		$this->execute();
	}

	/**
	 * Stores one ore more value(s).
	 *
	 * @param string $nameOrData   The name of the key, or the storable data in an associative array.
	 * @param mixed  $value        The value.
	 *
	 * @throws \Exception   If the key already exist.
	 *
	 * @return void
	 */
	protected function setToView($nameOrData, $value = null) {
		Application::getInstance()->getDiContainer()->getViewDo()->set($nameOrData, $value);
	}

	/**
	 * Executes the script.
	 */
	abstract protected function execute();
}