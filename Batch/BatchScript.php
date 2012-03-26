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
     * Helper method to execute the script
     */
    public static function run() {
        $application = Application::getInstance();
        $eventHandlerRegistry = $application->getDiContainer()->getEventHandlerRegistry();
        try {
            $eventHandlerRegistry->raise(new Event(Event::TYPE_APPSTART));
            $instance = new static();
            $instance->execute();
        } catch (\Exception $exception) {
            Application::getInstance()->getErrorHandlerRegistry()->handleException($exception);
        }
        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPFINISH));
    }

    /**
     * Executes the script.
     */
    abstract protected function execute();
}