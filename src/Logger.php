<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use MCP\Logger\Message\MessageFactory;
use MCP\Logger\Service\SyslogService;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var ServiceInterface
     */
    private $service;

    /**
     * @var MessageFactoryInterface
     */
    private $factory;

    /**
     * @param ServiceInterface $service
     * @param MessageFactoryInterface $factory
     */
    public function __construct(ServiceInterface $service = null, MessageFactoryInterface $factory = null)
    {
        $this->service = $service ?: $this->buildDefaultService();
        $this->factory = $factory ?: $this->buildDefaultFactory();
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $message = $this->factory->buildMessage($level, $message, $context);
        $this->service->send($message);
    }

    /**
     * @return ServiceInterface
     */
    protected function buildDefaultService()
    {
        return new SyslogService;
    }

    /**
     * @return MessageFactoryInterface
     */
    protected function buildDefaultFactory()
    {
        return new MessageFactory;
    }
}
