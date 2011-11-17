<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Log
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Log;
use YapepBase\Log\Message\IMessage;
use YapepBase\Exception\ConfigException;

/**
 * Syslog logger class
 *
 * Configuration:
 *     <ul>
 *         <li>applicationIdent: The name of the application as it will appear in the logs.</li>
 *         <li>facility: The facility to use for logging {@uses LOG_*}</li>
 *         <li>includeSapiName: If TRUE, the SAPI's name will be appended to the applicationIdent. Optional.</li>
 *         <li>addPid: If TRUE, the current PID will be logged too. Optional.</li>
 *         <li>printError: If TRUE, the log message will also be printed to STDERR. Optional.</li>
 *         <li>console: If TRUE, the message will be written to the sytem console,
 *             if there is an error while sending. Optional</li>
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Log
 */
class Syslog extends LoggerAbstract {

    /**
     * Logs the message
     *
     * @param IMessage $message
     */
    public function log(IMessage $message) {
        $ident = $this->configOptions['applicationIdent'];
        if (isset($this->configOptions['includeSapiName']) && $this->configOptions['includeSapiName']) {
            $ident .= '-' . PHP_SAPI;
        }

        // Set the options
        $options = 0;
        if (isset($this->configOptions['addPid']) && $this->configOptions['addPid']) {
            $options = $options | LOG_PID;
        }
        if (isset($this->configOptions['printError']) && $this->configOptions['printError']) {
            $options = $options | LOG_PERROR;
        }
        if (isset($this->configOptions['console']) && $this->configOptions['console']) {
            $options = $options | LOG_CONS;
        }

        $logMessage = $this->getLogMessage($message);

        openlog($ident, $options, $this->configOptions['facility']);
        syslog($message->getPriority(), $logMessage);
        closelog();
    }

    /**
     * Returns the log message prepared from the message
     *
     * @param IMessage $message
     *
     * @return string
     */
    protected function getLogMessage(IMessage $message) {
        $fields = $message->getFields();
        $logMessage = '[' . $message->getTag() . ']|';

        if (is_array($fields) && !empty($fields)) {
            $logMessage .= implode('|', $fields) . '|';
        }

        return $logMessage . $message->getMessage();

    }

    /**
     * Verifies the configuration. If there is an error with the config, it throws an exception.
     *
     * @param string $configName   The name of the configuration to validate.
     *
     * @throws \YapepBase\Exception\ConfigException   On configuration errors.
     */
    protected function verifyConfig($configName) {
        parent::verifyConfig();
        if (
            !is_array($this->configOptions) || empty($this->configOptions['facility'])
            || empty($this->configOptions['applicationIdent'])
        ) {
            throw new ConfigException('Configuration invalid for syslog: ' . $configName);
        }
    }





}