<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Batch
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Batch;

/**
 * Base class for creating batch scripts that should be ran in at most one instance.
 *
 * Uses the CliUserInterfaceHelper to parse/handle switches. Any subclasses should not use these switches for their
 * configuration, or override the parseSwitches() method.
 * The following switches are defined and parsed by the class:
 * <ul>
 * </ul>
 *
 * The following switches are defuned, but not parsed by the class:
 * <ul>
 *     <li>-e: Sets the environment. It should be used by the bootstrap, if onl
 * </ul>
 *
 *
 *
 * @package    YapepBase
 * @subpackage Batch
 */
class LockingBatchScript {


}