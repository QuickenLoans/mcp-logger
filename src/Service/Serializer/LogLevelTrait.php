<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service\Serializer;

use MCP\Logger\LogLevelInterface as QLLogLevel;
use Psr\Log\LogLevel as PSRLogLevel;

/**
 * Utility for converting a PSR-3 log level to QL log level.
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
}
