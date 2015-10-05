<?php

namespace MCP\Logger;

use GuzzleHttp\Client;
use MCP\Logger\Adapter\Psr\MessageFactory;
use MCP\Logger\Renderer\XmlRenderer;
use MCP\Logger\Service\Guzzle5Service;
use QL\UriTemplate\UriTemplate;

if (substr(Client::VERSION, 0, 1) !== '5') {
    echo sprintf('Guzzle 5 is required. %s is installed.', Client::VERSION);
    exit;
}

$silent = false;
$buffer = 3;

$uri = new UriTemplate('http://qlsonictest:2581/web/core/logentries');
$service = new Guzzle5Service(new Client, new XmlRenderer, $uri, $silent, false, $buffer);
$logger = new Logger($service, new MessageFactory);

$logger->info('mcp-logger : guzzle 5 test 1');
$logger->info('mcp-logger : guzzle 5 test 2');
$logger->info('mcp-logger : guzzle 5 test 3');

// The 4th message causes the buffer to hit the limit and flush
$logger->info('mcp-logger : guzzle 5 test 4');

echo <<<HTML

<br>Sent 4 log messages.
<br>Check <a href="http://core/app/200001?environment=Test">http://core/app/200001</a> for your messages

HTML;
