<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @author       Janos Pasztor <j.pasztor@ixolit.com>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

include_once(__DIR__ . '/../bootstrap.php');
\YapepBase\Application::getInstance()->getErrorHandlerContainer()->addErrorHandler(new \YapepBase\ErrorHandler\StrictErrorHandler());
