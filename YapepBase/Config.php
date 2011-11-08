<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase;

/**
 * Config singleton class
 *
 * @package    YapepBase
 */
class Config {

    /**
     * The singleton instance
     *
     * @var Config
     */
    protected static $instance;

    /**
     * The configuration data
     * ...
     * @var array
     */
    protected $configurationData = array();

    /**
     * Singleton constructor
     */
    protected function __construct() {
    }

    /**
     * Singleton clone method
     */
    protected function __clone() {
    }

    /**
     * Singleton getter
     *
     * @return Config
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Sets a configuration value
     *
     * @param string $data   Associative array containing the settings.
     *                       The key can be divided to sections with '.' as a separator.
     */
    public function set(array $data) {
        $this->configurationData = array_merge($this->configurationData, $data);
    }

    /**
     * Returns the setting specified by the name.
     *
     * The name can be a simple string, or can end in an '*' character for wildcard lookup.
     * In normal mode, if the setting is not found by the name, the specified default will be returned.
     * In wildcard mode the whole section that begins as the name before the wildcard, will be returned.
     * If no results are found, the default will be returned. If name is '*', all the settings are returned.
     *
     * @param string $name               The name of the setting to look for.
     * @param mixed  $default            If the setting is not found, this value will be returned.
     * @param bool   $keepOriginalName   If TRUE, the original key will be kept for wildcard names,
     *                                   if FALSE the key will begin at the wildcard in the result array.
     *
     * @return mixed|array   The result.
     */
    public function get($name, $default, $keepOriginalName = false) {
        if (empty($name)) {
            return $default;
        }

        if ('*' == $name) {
            // Return everything
            return $this->configurationData;
        }

        if (isset($this->configurationData[$name])) {
            return $this->configurationData[$name];
        }

        // If it ends with an asterix, return all the keys beginning with the name
        if ('.*' == substr($name, -2, 2)) {
            $name = substr($name, 0, -1);
            $result = array();
            foreach($this->configurationData as $key => $value) {
                if (0 === strpos($key, $name)) {
                    if (!$keepOriginalName) {
                        $key = substr($key, strlen($name) -2);
                    }
                    $result[$key] = $this->configurationData[$value];
                }
            }
            return (empty($result) ? $default : $result);
        }

        // No matches, return the default
        return $default;
    }

}