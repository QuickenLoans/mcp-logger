<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Serializer;

use Psr\Log\LogLevel as PSRLogLevel;

/**
 * Utility for:
 *
 * converting a PSR-3 log level to QL log level.
 *
 * - or -
 *
 * Converting PSR-3 log to Syslog system constant.
 */
trait LogLevelTrait
{
    /**
     * Translate a PRS-3 log level to QL log level
     *
     * Not used:
     *     - 'audit'
     *
     * @param string $severity
     *
     * @return string
     */
    public function convertLogLevelFromPSRToQL($severity)
    {
        // Equal mappings
        if ($severity === PSRLogLevel::DEBUG) {
            return 'debug';

        } elseif ($severity === PSRLogLevel::INFO) {
            return 'info';

        } elseif ($severity === PSRLogLevel::WARNING) {
            return 'warn';

        } elseif ($severity === PSRLogLevel::ERROR) {
            return 'error';
        }

        // Duplicate mappings
        if ($severity === PSRLogLevel::NOTICE) {
            return 'info';

        } elseif ($severity === PSRLogLevel::CRITICAL) {
            return 'fatal';

        } elseif ($severity === PSRLogLevel::ALERT) {
            return 'fatal';

        } elseif ($severity === PSRLogLevel::EMERGENCY) {
            return 'fatal';
        }

        // Default to error
        return 'error';
    }

    /**
     * Is a PSR-3 log severity disruptive to users?
     *
     * @param string $severity
     *
     * @return bool
     */
    public function isLogLevelDisruptive($severity)
    {
        $disruptives = [
            PSRLogLevel::ERROR,
            PSRLogLevel::CRITICAL,
            PSRLogLevel::ALERT,
            PSRLogLevel::EMERGENCY
        ];

        return in_array($severity, $disruptives, true);
    }

    /**
     * Translate a PRS-3 log level to Syslog log level
     *
     * @param string $severity
     *
     * @return int
     */
    public function convertLogLevelFromPSRToSyslog($severity)
    {
        switch ($severity) {
            case PSRLogLevel::DEBUG:
                return LOG_DEBUG;
            case PSRLogLevel::INFO:
                return LOG_INFO;
            case PSRLogLevel::NOTICE:
                return LOG_NOTICE;
            case PSRLogLevel::WARNING:
                return LOG_WARNING;
            case PSRLogLevel::ERROR:
                return LOG_ERR;
            case PSRLogLevel::CRITICAL:
                return LOG_CRIT;
            case PSRLogLevel::ALERT:
                return LOG_ALERT;
            case PSRLogLevel::EMERGENCY:
                return LOG_EMERG;
            default:
                return LOG_ERR;
        }
    }
}
