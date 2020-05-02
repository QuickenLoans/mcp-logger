<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * A logger that allows for broadcasting a log message to multiple other loggers.
 */
class BroadcastLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var LoggerInterface[]
     */
    private $loggers;

    /**
     * @param LoggerInterface[] $loggers
     */
    public function __construct(array $loggers = [])
    {
        $this->loggers = [];

        foreach ($loggers as $logger) {
            $this->addLogger($logger);
        }
    }

    /**
     * Add an additional logger to receive message broadcasts.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function addLogger(LoggerInterface $logger)
    {
        $this->loggers[] = $logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}
