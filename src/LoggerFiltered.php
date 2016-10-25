<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * A logger that allows for setting a minimum logging level
 */
class LoggerFiltered implements LoggerInterface
{
    use LoggerTrait;

    /**
     * Allowed log levels
     *
     * @var array
     */
    private $levels = [
        LogLevel::EMERGENCY => 7,
        LogLevel::ALERT => 6,
        LogLevel::CRITICAL => 5,
        LogLevel::ERROR => 4,
        LogLevel::WARNING => 3,
        LogLevel::NOTICE => 2,
        LogLevel::INFO => 1,
        LogLevel::DEBUG => 0
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
     * Set the log level
     *
     * When passing this value, you must make sure that it is a valid level as defined by Psr\Log\LogLevel. Any
     * other value will result in Psr\Log\LogLevel::DEBUG being set as the minimum value, and all messages will be
     * logged.
     *
     * @param $level
     */
    public function setLevel($level)
    {
        $this->level = array_key_exists($level, $this->levels) ? $level : LogLevel::DEBUG;
    }

    /**
     * Get the current log level
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
     * @return bool
     */
    private function shouldLog($level)
    {
        if (array_key_exists($level, $this->levels) && $this->levels[$level] < $this->levels[$this->level]) {
            return false;
        }

        return true;
    }
}
