<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use Exception as BaseException;
use MCP\Logger\Exception;
use MCP\Logger\MessageInterface;
use MCP\Logger\RendererInterface;
use MCP\Logger\Renderer\XmlRenderer;
use MCP\Logger\ServiceInterface;
use QL\MCP\Http\ClientInterface;
use QL\MCP\Http\Pool;

/**
 * Logging service for MCP Http
 */
class HttpService implements ServiceInterface
{
    use BufferedServiceTrait;

    // Configuration Keys
    const CONFIG_SILENT         = 'silent';
    const CONFIG_BUFFER_LIMIT   = 'buffer_limit';
    const CONFIG_SHUTDOWN       = 'shutdown';
    const CONFIG_TEMPLATE       = 'template';
    const CONFIG_SCHEME         = 'scheme';
    const CONFIG_HOSTNAME       = 'hostname';
    const CONFIG_PORT           = 'port';
    const CONFIG_ROOT           = 'root';
    const CONFIG_RESOURCE       = 'resource';

    // Configuration Defaults
    const DEFAULT_SILENT        = true;
    const DEFAULT_BUFFER_LIMIT  = 0;
    const DEFAULT_SHUTDOWN      = true;
    const DEFAULT_TEMPLATE      = '{scheme}://{hostname}:{port}{/root}/{+resource}';
    const DEFAULT_SCHEME        = 'http';
    const DEFAULT_PORT          = 2581;
    const DEFAULT_ROOT          = 'web/core';
    const DEFAULT_RESOURCE      = 'logentries';

    // Error Messages
    const ERR_CONFIG            = 'Missing required configuration key %s.';
    const ERR_SENDING           = 'Unable to send %s (of %s) log messages. Service returned errors. %s';

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param Pool $pool
     * @param RendererInterface $renderer
     * @param array $configuration
     * @throws Exception
     */
    public function __construct(Pool $pool, RendererInterface $renderer = null, array $configuration = [])
    {
        if (!array_key_exists(self::CONFIG_HOSTNAME, $configuration)) {
            throw new Exception(sprintf(self::ERR_CONFIG, self::CONFIG_HOSTNAME));
        }

        $this->configuration = array_merge([
            self::CONFIG_SILENT => self::DEFAULT_SILENT,
            self::CONFIG_BUFFER_LIMIT => self::DEFAULT_BUFFER_LIMIT,
            self::CONFIG_SHUTDOWN => self::DEFAULT_SHUTDOWN,
            self::CONFIG_TEMPLATE => self::DEFAULT_TEMPLATE,
            self::CONFIG_SCHEME => self::DEFAULT_SCHEME,
            self::CONFIG_PORT => self::DEFAULT_PORT,
            self::CONFIG_ROOT => self::DEFAULT_ROOT,
            self::CONFIG_RESOURCE => self::DEFAULT_RESOURCE
        ], $configuration);

        $this->pool = $pool;
        $this->renderer = $renderer ?: new XmlRenderer;

        $this->initializeBuffer(
            $this->configuration[self::CONFIG_BUFFER_LIMIT],
            $this->configuration[self::CONFIG_SHUTDOWN]
        );
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message)
    {
        $this->append($message);
    }

    /**
     * @param MessageInterface $message
     * @return array
     */
    private function createRequest(MessageInterface $message)
    {
        return $this->pool->createRequest('POST', $this->configuration[self::CONFIG_TEMPLATE], [
            ClientInterface::BODY => call_user_func($this->renderer, $message),
            ClientInterface::HEADERS => [
                'Content-Type' => $this->renderer->contentType()
            ],
            ClientInterface::URI_VARIABLES => [
                self::CONFIG_SCHEME => $this->configuration[self::CONFIG_SCHEME],
                self::CONFIG_HOSTNAME => $this->configuration[self::CONFIG_HOSTNAME],
                self::CONFIG_PORT => $this->configuration[self::CONFIG_PORT],
                self::CONFIG_ROOT => $this->configuration[self::CONFIG_ROOT],
                self::CONFIG_RESOURCE => $this->configuration[self::CONFIG_RESOURCE],
            ]
        ]);
    }

    /**
     * @param array $messages
     * @return void
     * @throws Exception
     */
    private function handleBatch(array $messages)
    {
        $errors = [];

        foreach ($this->pool->batch($messages, [ClientInterface::EXPECT_STATUS => 200]) as $result) {
            if ($result instanceof BaseException) {
                $errors[] = $result;
            }
        }

        if (count($errors) > 0) {
            $message = sprintf(self::ERR_SENDING, count($errors), count($messages), implode(', ', array_unique(array_map(function (BaseException $e) {
                return $e->getMessage();
            }, $errors))));

            if ($this->configuration[self::CONFIG_SILENT]) {
                error_log($message);
            } else {
                throw new Exception($message);
            }
        }
    }
}
