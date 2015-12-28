<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use MCP\Logger\Adapter\Psr\MessageFactory;
use MCP\Logger\MessageInterface;
use MCP\Logger\ServiceInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * @api
 */
class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var ServiceInterface
     */
    private $service;

    /**
     * @var MessageFactory
     */
    private $factory;

    /**
     * @param ServiceInterface $service
     * @param MessageFactory $factory
     */
    public function __construct(ServiceInterface $service, MessageFactory $factory)
    {
        $this->service = $service;
        $this->factory = $factory;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $message = $this->factory->buildMessage($level, $message, $context);
        $this->service->send($message);
    }
}
