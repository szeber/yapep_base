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
     * Can set a single option when using both parameters, or multiple configuration options as an associative array.
     *
     * @param string|array $dataOrName    Name of the setting or an associative array containing the settings.
     *                                    The key can be divided to sections with '.' as a separator.
     * @param mixed        $value         The value of the setting if setting a single configuration option.
     *                                    Not used when setting multiple options.
     */
    public function set($dataOrName, $value = null) {
        if (is_array($dataOrName)) {
            $this->configurationData = array_merge($this->configurationData, $dataOrName);
        } else {
            $this->configurationData[(string)$dataOrName] = $value;
        }
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
    public function get($name, $default = null, $keepOriginalName = false) {
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
                        $key = substr($key, strlen($name));
                    }
                    $result[$key] = $value;
                }
            }
            return (empty($result) ? $default : $result);
        }

        // No matches, return the default
        return $default;
    }

    /**
     * Removes a configuration setting
     *
     * @param string $name
     */
    public function delete($name) {
        if (isset($this->configurationData[$name])) {
            unset($this->configurationData[$name]);
        }
    }

    /**
     * Clears all configuration.
     */
    public function clear() {
        $this->configurationData = array();
    }

}