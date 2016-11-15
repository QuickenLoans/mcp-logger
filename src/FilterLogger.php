<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * A logger that allows for setting a minimum logging level.
 *
 * You must provide another PSR-3 Logger that this logger wraps.
 */
class FilterLogger implements LoggerInterface
{
    use LoggerTrait;

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
     * @var string
     */
    private $level;

    /**
     * @param LoggerInterface $logger
     * @param string $level
     */
    public function __construct(LoggerInterface $logger, $level = null)
    {
        $this->logger = $logger;
        $this->setLevel($level);
    }

    /**
     * Set the minimum log level
     *
     * When passing this value, you must make sure that it is a valid level as defined by Psr\Log\LogLevel. Any
     * other value will result in Psr\Log\LogLevel::DEBUG being set as the minimum value, and all messages will be
     * logged.
     *
     * @param $level
     *
     * @return void
     */
    public function setLevel($level)
    {
        $this->level = array_key_exists($level, self::LEVEL_PRIORITIES) ? $level : LogLevel::DEBUG;
    }

    /**
     * Get the current minimum log level
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
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
        if ($this->shouldLog($level)) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Return true if the provided log level meets or exceeds the minimum logging level.
     *
     * @param string $level
     *
     * @return bool
     */
    private function shouldLog($level)
    {
        if (!array_key_exists($level, self::LEVEL_PRIORITIES)) {
            return true;
        }

        if (self::LEVEL_PRIORITIES[$level] > self::LEVEL_PRIORITIES[$this->level]) {
            return false;
        }

        return true;
    }
}
