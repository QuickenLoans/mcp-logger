<?php

namespace QL\MCP\Logger;

use Composer\Autoload\ClassLoader;
use QL\MCP\Logger\MessageInterface;
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\Service\Serializer\LineSerializer;
use QL\MCP\Logger\Service\SyslogService;

if (!class_exists(ClassLoader::class)) {
    $autoloader = require __DIR__ . '/../../vendor/autoload.php';
}

$ident = 'myapplication';
$facility = LOG_USER;

$service = new SyslogService(new LineSerializer, [
    'ident' => $ident,
    'facility' => $facility,
    'silent' => false
]);

$factory = new MessageFactory;
$factory->setDefaultProperty(MessageInterface::SERVER_ENVIRONMENT, 'test');
$factory->setDefaultProperty(MessageInterface::APPLICATION_ID, '200001');

$logger = new Logger($service, $factory);

// PLEASE NOTE:
$logger->warning('mcp-logger : syslog test 1');
$logger->warning('mcp-logger : syslog test 2');

echo <<<HTML

<br>Sent 2 log messages.

<br>For LINUX: Check <code>/var/log/messages</code> for your messages.

<br>For MACOS: Open <code>console.app</code> to check <code>system.log</code>.
               Please note only message NOTICE and above are logged.

HTML;
