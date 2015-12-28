<?php

namespace MCP\Logger;

use MCP\Logger\Adapter\Psr\MessageFactory;
use MCP\Logger\Renderer\JsonRenderer;
use MCP\Logger\Service\SyslogService;

$ident = 'myapplication';
$facility = LOG_USER;

$service = new SyslogService(new JsonRenderer, ['ident' => $ident, 'facility' => $facility]);
$fact = new MessageFactory;
$fact->setDefaultProperty('environment', 'test');

$logger = new Logger($service, $fact);

$logger->info('mcp-logger : syslog test 1');
$logger->info('mcp-logger : syslog test 2');

echo <<<HTML

<br>Sent 2 log messages.
<br>Check <code>/var/log/messages</code> for your messages

HTML;
