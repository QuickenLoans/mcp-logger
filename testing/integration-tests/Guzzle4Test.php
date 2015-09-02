<?php

namespace MCP\Logger;

use GuzzleHttp\Client;
use MCP\Logger\Adapter\Psr\MessageFactory;
use MCP\Logger\Renderer\XmlRenderer;
use MCP\Logger\Service\Guzzle4Service;
use QL\UriTemplate\UriTemplate;

if (substr(Client::VERSION, 0, 1) !== '4') {
    echo sprintf('Guzzle 4 is required. %s is installed.', Client::VERSION);
    exit;
}

$uri = new UriTemplate('http://qlsonictest:2581/web/core/logentries');
$service = new Guzzle4Service(new Client, new XmlRenderer, $uri);
$logger = new Logger($service, new MessageFactory);

$logger->info('mcp-logger : guzzle 4 test 1');
$logger->info('mcp-logger : guzzle 4 test 2');

echo "\n<br>Sent 2 log messages.";
