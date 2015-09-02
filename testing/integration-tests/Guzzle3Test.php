<?php

namespace MCP\Logger;

use Guzzle\Http\Client;
use MCP\Logger\Adapter\Psr\MessageFactory;
use MCP\Logger\Renderer\XmlRenderer;
use MCP\Logger\Service\Guzzle3Service;
use QL\UriTemplate\UriTemplate;

if (!class_exists(Client::CLASS)) {
    echo 'Guzzle 3 is required.';
    exit;
}

$uri = new UriTemplate('http://qlsonictest:2581/web/core/logentries');
$service = new Guzzle3Service(new Client, new XmlRenderer, $uri);
$logger = new Logger($service, new MessageFactory);

$logger->info('mcp-logger : guzzle 3 test 1');
$logger->info('mcp-logger : guzzle 3 test 2');

echo "\n<br>Sent 2 log messages.";
