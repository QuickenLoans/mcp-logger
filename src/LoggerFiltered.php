<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * A logger that allows for setting a minimum logging level
 */
class LoggerFiltered implements LoggerInterface
{
    use LoggerTrait;
    use LogLevelFilterTrait;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param string $minimum
     */
    public function __construct(LoggerInterface $logger, $minimum = null)
    {
        $this->logger = $logger;
        $this->minimum = $minimum === null ? LogLevel::DEBUG : $minimum;
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
}
