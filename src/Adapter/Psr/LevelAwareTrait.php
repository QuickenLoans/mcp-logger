<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Adapter\Psr;

use Psr\Log\LogLevel;

/**
 * @internal
 */
trait LevelAwareTrait
{
    /**
     * Translate a psr-3 log level to core log level
     *
     * Not used:
     *     - static::AUDIT
     *
     * @param string $level
     * @return string|null
     */
    public function convertPsr3LogLevel($level)
    {
        if ($level === LogLevel::DEBUG) {
            return static::DEBUG;
        }

        if (in_array($level, array(LogLevel::INFO, LogLevel::NOTICE), true)) {
            return static::INFO;
        }

        if ($level === LogLevel::WARNING) {
            return static::WARN;
        }

        if ($level === LogLevel::ERROR) {
            return static::ERROR;
        }

        if (in_array($level, array(LogLevel::CRITICAL, LogLevel::ALERT, LogLevel::EMERGENCY), true)) {
            return static::FATAL;
        }

        return null;
    }

    /**
     * Translate a core log level to psr-3 log level
     *
     * Not used:
     *     - LogLevel::NOTICE
     *     - LogLevel::ALERT
     *     - LogLevel::EMERGENCY
     *
     * @param string $level
     * @return string|null
     */
    public function convertCoreLogLevel($level)
    {
        if ($level === static::DEBUG) {
            return LogLevel::DEBUG;
        }

        if (in_array($level, array(static::INFO, static::AUDIT), true)) {
            return LogLevel::INFO;
        }

        if ($level === static::WARN) {
            return LogLevel::WARNING;
        }

        if ($level === static::ERROR) {
            return LogLevel::ERROR;
        }

        if ($level === static::FATAL) {
            return LogLevel::CRITICAL;
        }

        return null;
    }
}
