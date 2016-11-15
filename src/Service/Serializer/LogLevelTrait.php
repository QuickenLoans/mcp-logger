<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service\Serializer;

use Psr\Log\LogLevel as PSRLogLevel;
use QL\MCP\Logger\QLLogLevel;

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
     *     - QLLogLevel::AUDIT
     *
     * @param string $level
     *
     * @return string
     */
    public function convertLogLevelFromPSRToQL($level)
    {
        // Equal mappings
        if ($level === PSRLogLevel::DEBUG) {
            return QLLogLevel::DEBUG;

        } elseif ($level === PSRLogLevel::INFO) {
            return QLLogLevel::INFO;

        } elseif ($level === PSRLogLevel::WARNING) {
            return QLLogLevel::WARNING;

        } elseif ($level === PSRLogLevel::ERROR) {
            return QLLogLevel::ERROR;
        }

        // Duplicate mappings
        if ($level === PSRLogLevel::NOTICE) {
            return QLLogLevel::INFO;

        } elseif ($level === PSRLogLevel::CRITICAL) {
            return QLLogLevel::FATAL;

        } elseif ($level === PSRLogLevel::ALERT) {
            return QLLogLevel::FATAL;

        } elseif ($level === PSRLogLevel::EMERGENCY) {
            return QLLogLevel::FATAL;
        }

        // Default to error
        return QLLogLevel::ERROR;
    }

    /**
     * Translate a PRS-3 log level to Syslog log level
     *
     * @param string $level
     *
     * @return int
     */
    public function convertLogLevelFromPSRToSyslog($level)
    {
        switch ($level) {
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
