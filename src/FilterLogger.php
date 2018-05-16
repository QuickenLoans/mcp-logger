<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * A logger that allows for setting a lowest priority logging level.
 *
 * PLEASE NOTE:
 * As per RFC 5424, severity 0 (emergency) is HIGHER PRIORITY than severity 7 (debug).
 *
 * See RFC 5424 Section 6.2.1 for more details on Syslog severity levels and their priority:
 * https://tools.ietf.org/html/rfc5424#section-6.2.1
 *
 * ```
 * PSR-3              Numerical   Severity
 *                       Code
 *
 * LogLevel::EMERGENCY    0       Emergency: system is unusable
 * LogLevel::ALERT        1       Alert: action must be taken immediately
 * LogLevel::CRITICAL     2       Critical: critical conditions
 * LogLevel::ERROR        3       Error: error conditions
 * LogLevel::WARNING      4       Warning: warning conditions
 * LogLevel::NOTICE       5       Notice: normal but significant condition
 * LogLevel::INFO         6       Informational: informational messages
 * LogLevel::DEBUG        7       Debug: debug-level messages
 *  ```
 *
 * You must provide another PSR-3 Logger that this logger proxies messages to.
 */
class FilterLogger implements LoggerInterface
{
    use LoggerTrait;

    const ERR_INVALID_LEVEL = 'Invalid Log level provided. Ensure you are using PSR-3 log levels as defined in Psr\Log\LogLevel';

    /**
     * Log severities in ranked priority.
     *
     * @var array
     */
    const LEVEL_PRIORITIES = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $acceptedLevels;

    /**
     * @param LoggerInterface $logger
     * @param string $level
     */
    public function __construct(LoggerInterface $logger, $level = LogLevel::DEBUG)
    {
        $this->logger = $logger;
        $this->setLevel($level);
    }

    /**
     * Set the lowest priority log level.
     *
     * When setting this value, ensure it is a valid level as defined in `Psr\Log\LogLevel`.
     *
     * @param string $level
     *
     * @throws Exception
     *
     * @return void
     */
    public function setLevel($level)
    {
        if (!array_key_exists($level, self::LEVEL_PRIORITIES)) {
            throw new Exception(self::ERR_INVALID_LEVEL);
        }

        $lowestPriority = self::LEVEL_PRIORITIES[$level];
        $accepted = [];

        foreach (self::LEVEL_PRIORITIES as $level => $priority) {
            if ($priority > $lowestPriority) {
                continue;
            }

            $accepted[] = $level;
        }

        $this->acceptedLevels = $accepted;
    }

    /**
     * Logs with an arbitrary level.
     *
     * Note that the specified logging level must be a Psr\Log\LogLevel value.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        if (in_array($level, $this->acceptedLevels, true)) {
            $this->logger->log($level, $message, $context);
        }
    }
}
