<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Syslog
 * @author       Janos Pasztor <j.pasztor@ixolit.com>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Syslog;

/**
 * This class is a container for generic Syslog (client and server) functions and constants.
 */
class Syslog {
    /**
     * Security/authorization messages
     */
    const LOG_AUTH = 40;
    /**
     * Security/authorization messages (private)
     */
    const LOG_AUTHPRIV = 80;
    /**
     * Cron daemon
     */
    const LOG_CRON = 72;
    /**
     * Other daemons
     */
    const LOG_DAEMON = 24;
    /**
     * FTP server
     */
    const LOG_FTP = 88;
    /**
     * Kernel log (do not use)
     */
    const LOG_KERN = 0;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL0 = 128;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL1 = 136;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL2 = 144;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL3 = 152;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL4 = 160;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL5 = 168;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL6 = 176;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL7 = 184;
    /**
     * Printer facility
     */
    const LOG_LPR = 48;
    /**
     * Mail server
     */
    const LOG_MAIL = 16;
    /**
     * News server
     */
    const LOG_NEWS = 72;
    /**
     * Syslog internal messages (do not use)
     */
    const LOG_SYSLOG = 48;
    /**
     * Generic user-level messages
     */
    const LOG_USER = 8;
    /**
     * UUCP subsystem
     */
    const LOG_UUCP = 64;

    /**
     * System is unusable
     */
    const LOG_EMERG = 0;
    /**
     * Action must be taken immediately
     */
    const LOG_ALERT = 1;
    /**
     * Critical conditions
     */
    const LOG_CRIT = 2;
    /**
     * Error conditions
     */
    const LOG_ERR = 3;
    /**
     * Warning conditions
     */
    const LOG_WARNING = 4;
    /**
     * Normal, but significant condition
     */
    const LOG_NOTICE = 5;
    /**
     * Informational message
     */
    const LOG_INFO = 6;
    /**
     * Debug-level message
     */
    const LOG_DEBUG = 7;
}
