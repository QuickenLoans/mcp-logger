<?php

namespace MCP\Logger\Service;

use GuzzleHttp\ClientInterface;
use MCP\Logger\MessageInterface;
use MCP\Logger\RendererInterface;
use MCP\Logger\ServiceInterface;
use QL\UriTemplate\UriTemplate;

class Guzzle6Service implements ServiceInterface
{
    use BufferedServiceTrait;

    // Configuration Keys
    // @todo

    // Configuration Defaults
    // @todo

    // Error Messages
    // @todo

    public function __construct(
        ClientInterface $guzzle,
        RendererInterface $renderer,
        UriTemplate $uri,
        array $configuration = []
    ) {
        // @todo
    }

    public function send(MessageInterface $message)
    {
        $this->append($message);
    }

    private function createRequest(MessageInterface $message)
    {
        // @todo
    }

    private function handleBatch($messages)
    {
        // @todo
    }
}