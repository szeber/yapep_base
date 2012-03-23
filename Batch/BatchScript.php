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
 * BatchScript class
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
            trigger_error('Unhandled exception of type: ' . get_class($exception) .'. Message: '
                . $exception->getMessage(), E_USER_ERROR);
        }
        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPFINISH));
    }

    /**
     * Executes the script.
     */
    abstract protected function execute();
}