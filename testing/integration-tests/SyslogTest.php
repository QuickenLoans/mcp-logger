<?php

namespace MCP\Logger;

use MCP\Logger\Adapter\Psr\MessageFactory;
use MCP\Logger\Renderer\JsonRenderer;
use MCP\Logger\Service\SyslogService;

$ident = '';
$facility = LOG_USER;

$service = new SyslogService(new JsonRenderer, ['ident' => $ident, 'facility' => LOG_USER]);
$logger = new Logger($service, new MessageFactory);

$logger->info('mcp-logger : syslog test 1');
$logger->info('mcp-logger : syslog test 2');

echo "\n<br>Sent 2 log messages.";
