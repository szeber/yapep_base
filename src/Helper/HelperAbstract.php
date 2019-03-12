<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Helper;

use YapepBase\Application;

/**
 * Abstract base class for helpers
 */
abstract class HelperAbstract
{
    /**
     * Translates the specified string.
     *
     * @param string $string       The string.
     * @param array  $parameters   The parameters for the translation.
     * @param string $language     The language.
     *
     * @return string
     */
    protected function _($string, $parameters = [], $language = null)
    {
        return Application::getInstance()->getI18nTranslator()->translate(
            get_class($this),
            $string,
            $parameters,
            $language
        );
    }
}
