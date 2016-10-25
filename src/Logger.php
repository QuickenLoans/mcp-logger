<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * @api
 */
class Logger implements LoggerInterface
{
    use LoggerTrait;
    use LogLevelFilterTrait;

    /**
     * Configuration key names
     */
    const MINIMUM_LEVEL = 'minimum.level';

    /**
     * @var ServiceInterface
     */
    private $service;

    /**
     * @var MessageFactoryInterface
     */
    private $factory;

    /**
     * @var array
     */
    private $config;

    /**
     * @param ServiceInterface $service
     * @param MessageFactoryInterface $factory
     * @param array $config
     */
    public function __construct(ServiceInterface $service, MessageFactoryInterface $factory, $config = [])
    {
        $this->service = $service;
        $this->factory = $factory;

        $this->config = array_merge([
            self::MINIMUM_LEVEL => LogLevelInterface::DEBUG
        ], $config);

        $this->setMinimumLevel($this->config[self::MINIMUM_LEVEL]);
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

        if ($this->shouldLog($message->level())) {
            $this->service->send($message);
        }
    }
}
