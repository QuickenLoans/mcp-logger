[![CircleCI](https://img.shields.io/circleci/project/github/quickenloans-mcp/mcp-logger.svg?label=circleci)](https://circleci.com/gh/quickenloans-mcp/mcp-logger)
[![Latest Stable Version](https://img.shields.io/packagist/v/ql/mcp-logger.svg?label=stable)](https://packagist.org/packages/ql/mcp-logger)
[![GitHub License](https://img.shields.io/github/license/quickenloans-mcp/mcp-logger.svg)](https://packagist.org/packages/ql/mcp-logger)
![GitHub Language](https://img.shields.io/github/languages/top/quickenloans-mcp/mcp-logger.svg)

# MCP Logger

Structured logging for Quicken Loans PHP projects.

We prefer structured log messages defined on top of [PSR-3 Logger Interface](http://www.php-fig.org/psr/psr-3/) to allow easier searching of log messages from our log provider.

This library shares some features with the popular PHP logging library **Monolog**, but we aim to keep this library small and simple and avoid unnecessary dependencies for our projects.

## Contents

- [Installation](#installation)
- [Components](#components)
- [Usage](#usage)
    - [Logger](#logger)
    - [Filter Logger](#filter-logger)
    - [Broadcast Logger](#broadcast-logger)
    - [Memory Logger](#memory-logger)
    - [Creating a Message](#creating-a-message)
    - [Sending a Message](#sending-a-message)
- [Services](#services)
    - [ErrorLog Service](#errorlog-service) **RECOMMENDED LOGGING METHOD**
    - [Syslog Service](#syslog-service)
    - [Guzzle Service](#guzzle-service) (For Guzzle 6)
    - [Null Service](#null-service) (To ignore messages)
- [Log Message](#log-message)

## Installation

Run the following commands.

```bash
composer require ql/mcp-logger ~4.0
```

### Usage with Symfony

A symfony DI config is included with this library. This allows for easily including mcp logger components and using them within your app. *Please Note: this uses the fluent PHP format which requires symfony ~3.4 or greater.*

Add the following somewhere to your symfony DI yml configuration:
```yaml
imports:
    - resource: ../vendor/ql/mcp-logger/config/config.php
```

Then you can use the following services throughout your DI:

- `@mcp_logger` - PSR-3 logger
    - By default this uses the **ErrorLog** service (This should send errors to your web server by default).
- `@mcp_logger_factory` - Message factory
    - Append additional default parameters to configure the factory.

See [config/config.php](config/config.php) for all the services and parameters available. Most parameters can be configured using environment variables.

#### Configure logger through the following parameters:

- `%mcp_logger.default_properties%` - Default message properties
   > Example:
   > ```yaml
   > parameters:
   >     mcp_logger.default_properties:
   >        applicationID: 12345
   >        serverEnvironment: staging
   > ```

All other parameters can be specified through environment variables. If you are not familiar with using environment variables with Symfony DI: These require using the `symfony/dotenv` component. If you do not use `dotenv`, you can also statically set the config using a parameter such as `env(MCP_LOGGER_CONFIG_NAME)`.

- `MCP_LOGGER_SERVICE` - one of `[error_log, syslog, guzzle, null]` (default: `error_log`)
- `MCP_LOGGER_SERIALIZER` - one of `[json, line, xml]` (default: `line`)

- `MCP_LOGGER_MAX_SIZE_KB` - `100`. Max allowed size of log properties.
- `MCP_LOGGER_NEWLINES_ENABLED` - `SPLIT_ON_NEWLINES` or blank. Break messages on newlines and send individually to your log service (Very useful for file-based logs).
- Configuring **Line Serializer**
    - `MCP_LOGGER_LINE_SERIALIZER_TEMPLATE` - See [LineSerializer](src/Serializer/LineSerializer.php) for format.
    - `MCP_LOGGER_LINE_SERIALIZER_NEWLINES_ENABLED` - `ALLOW_NEWLINES` or blank.
- Configuring **Error Log**
    - `MCP_LOGGER_ERRORLOG_TYPE` - one of `[OPERATING_SYSTEM, SAPI, FILE]` (default: `OPERATING_SYSTEM`)
    - `MCP_LOGGER_ERRORLOG_FILE` - When using `FILE`, this must be a file path (blank by default).

- Configuring **Syslog**
    - `MCP_LOGGER_SYSLOG_IDENT` - Defaults to `mcplogger`
    - `MCP_LOGGER_SYSLOG_FACILITY` - See [php.net](http://php.net/manual/en/function.openlog.php)
    - `MCP_LOGGER_SYSLOG_OPTIONS` - See [php.net](http://php.net/manual/en/function.openlog.php)

- Configuring **Guzzle**
    - `MCP_LOGGER_GUZZLE_ENDPOINT` - HTTP endpoint to POST messages to.

#### Changing logger service

To change the service or serializer used by the logger (if you do not want to use the default) simply change the variable in your `.env` file.
```bash
MCP_LOGGER_SERVICE="guzzle"
MCP_LOGGER_SERIALIZER="json"

MCP_LOGGER_GUZZLE_ENDPOINT="https://logs.example.com/endpoint"
```

## Components

The MCP Logger consists of three main components:

- [QL\MCP\Logger\MessageInterface](src/MessageInterface.php)
- [QL\MCP\Logger\SerializerInterface](src/SerializerInterface.php)
- [QL\MCP\Logger\ServiceInterface](src/ServiceInterface.php)

To put things simply, a `Serializer` serializes a `Message` that is sent by a `Service`. Additionally, several convenience classes are also available to make connecting the pieces easier.

## Usage

- [Logger](#logger)
- [Filter Logger](#filter-logger)
- [Broadcast Logger](#broadcast-logger)
- [Memory Logger](#memory-logger)
- [Creating a Message](#creating-a-message)
- [Sending a Message](#sending-a-message)

```php
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\Serializer\JSONSerializer;
use QL\MCP\Logger\Service\SyslogService;
use QL\MCP\Logger\Logger;

// Basic setup, uses ErrorLog by default.
$logger = new Logger;

// To customize your options, build the factory and services and inject into the Logger.
$service = new SyslogService([
    SyslogService::CONFIG_IDENT => 'mytestapp'
]);

$serializer = new JSONSerializer;

$factory = new MessageFactory;
$factory->setDefaultProperty('applicationID', '123456');
$factory->setDefaultProperty('serverEnvironment', 'staging');
$factory->setDefaultProperty('serverHostname', 'mydevbox');

$loggerCustom = new Logger($service, $serializer, $factory);

// Send psr-3 logs
$logger->info('Hello World!');

$loggerCustom->error('Hello Major Tom, are you receiving me?', [
    'errCode' => 42
]);
```

### Filter Logger

If you want to set a minimum logging severity and only log message that meet or exceed that level, you may also want to use the `FilterLogger` class. This wraps the main logger and provides that filtering functionality.

```php
use Psr\Log\LogLevel;
use QL\MCP\Logger\FilterLogger;
use QL\MCP\Logger\Logger;

$logger = new FilterLogger(new Logger, LogLevel::WARNING);

// will not be logged because info < warning.
$logger->info('Hello World!');

// will be logged because error > warning.
$logger->error('Got a big problem over here!');
```

This is useful for changing the type of messages you want logged between environments or using runtime configuration to change the level of detail in your logs on the fly.

### Broadcast Logger

The `BroadcastLogger` class allows for broadcasting a single log message out to multiple logger services. Combined with the [Filter Logger](#filter-logger), this can allow for sending high priority messages to an on-call alerting service, and lower priority messages such as `debug` or `info` to a lower priority alerting service.

```php
use QL\MCP\Logger\BroadcastLogger;
use QL\MCP\Logger\Logger;

$logger = new BroadcastLogger([
    new Logger,
    new Logger
]);

// Messages sent to both loggers.
$logger->info('Hello World!');
```

### Memory Logger

Sometimes it can be useful to keep messages in memory, for example attaching them to the response in `debug` modes and rendering them onto the page (such as when using symfony profiler).

```php
use QL\MCP\Logger\MemoryLogger;
use QL\MCP\Logger\Logger;
use QL\MCP\Logger\Serializer\LineSerializer;

$serializer = new LineSerializer;
$logger = new MemoryLogger($serializer);

// Messages sent to both loggers.
$logger->info('Hello World!');
$logger->emergency('Hello World!');

$messages = $logger->getMessages();
# [
#   "serialized message",
#   "serialized message",
# ]
```

### Creating a Message

You can either create message objects manually or use the message factory.

#### Manually

There are 2 required fields to create a message. Everything else can be specified in an array as the 3rd parameter.

```php
use Psr\Log\LogLevel;
use QL\MCP\Common\Time\TimePoint;
use QL\MCP\Logger\Message\Message;
use QL\MCP\Logger\Serializer\LineSerializer;

$message = new Message(LogLevel::NOTICE, 'This is a messsage', [
    Message::CREATED => new TimePoint(2013, 8, 15, 0, 0, 0, 'UTC'),

    Message::APPLICATION_ID => '1234',
    Message::SERVER_IP => '127.0.0.1',
    Message::SERVER_HOSTNAME => 'mydevbox'
]);

// serialize the message
$serializer = new LineSerializer;
echo $serializer($message);
```

#### Using the Factory

Alternatively, a convenience factory is provided that will allow you to pass message defaults at setup so you do not have to populate these fields every time a message is logged.

```php
use Psr\Log\LogLevel;
use QL\MCP\Logger\Message\MessageFactory;

$factory = new MessageFactory;

$message = $factory->buildMessage(LogLevel::DEBUG, 'A debug message', [
    'test' => 'extra data',
    'test2' => 'data'
]);
```

When using the factory, there are several ways for you to add data to a message.

```php
use Psr\Log\LogLevel;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Logger\Message\MessageFactory;

// In the constructor
$factory = new MessageFactory([
    'applicationID' => '1',
    'serverIP' => IPv4Address::create('127.0.0.1')
]);

// With a setter
$factory->setDefaultProperty('serverHostname', 'ServerName');

// As context data when building the message
$message = $factory->buildMessage(LogLevel::DEBUG, 'A debug message', [
    'userIP' => IPv4Address::create('127.0.0.1')
]);
```

Unknown context that are not part of the standard properties of `MessageInterface` will be automatically added to `context` by the factory.

### Sending a Message

Once you have a service and a message, sending is easy.

```php
use MCP\Logger\Logger;
use QL\MCP\Logger\Serializer\LineSerializer;
use QL\MCP\Logger\Service\ErrorLogService;

$serializer = new LineSerializer;

$service = new ErrorLogService([
    ErrorLogService::CONFIG_TYPE => ErrorLogService::FILE,
    ErrorLogService::CONFIG_FILE => '/var/log/myappmessages'
]);

$formatted = $serializer($message);
$service->send($message->severity(), $formatted);
```

## Services

### ErrorLog Service

This service sends log messages through the php function `error_log()` [documentation](http://php.net/manual/en/function.error-log.php). ErrorLog easily provides the ability to send messages to the OS, SAPI (such as NGINX), or a file.

This service should be used `JSONSerializer` or `LineSerializer`.

```php
use MCP\Logger\Logger;
use MCP\Logger\Serializer\LineSerializer;
use MCP\Logger\Service\ErrorLogService;

$serializer = new LineSerializer;

$service = new ErrorLogService([
    ErrorLogService::CONFIG_TYPE => ErrorLogService::FILE
    ErrorLogService::CONFIG_FILE => '/var/log/mcplogger'
]);

$logger = new Logger($service, $serializer);
$logger->error('The MCP is the most efficient way of handling what we do!');
```

#### Syslog Service

This service allows you to send log messages directly to Syslog. From there, messages can be sent to a file, central Syslog server, or to a log collector such as Splunk or LogEntries. These destinations must be configured by
system administrators.

This service should be used `JSONSerializer` or `LineSerializer`.

```php
use MCP\Logger\Logger;
use MCP\Logger\Serializer\JSONSerializer;
use MCP\Logger\Service\SyslogService;

$serializer = new JSONSerializer;
$service = new SyslogService([
    // Ident SHOULD always be provided, to allow log filtering at the system-level.
    SyslogService::CONFIG_IDENT => 'MyAppName',
    SyslogService::CONFIG_FACILITY => LOG_USER,
    SyslogService::CONFIG_OPTIONS => LOG_ODELAY | LOG_CONS
]);

$logger = new Logger($service, $serializer);

$logger->error("That MCP, that's half our problem right there.");
```

### Guzzle Service

This service sends log message over HTTP with Guzzle. Guzzle 6 is currently supported.

This service should be used `JSONSerializer` or `XMLSerializer`.

```php
use GuzzleHttp\Client;
use MCP\Logger\Logger;
use MCP\Logger\Serializer\XMLSerializer;
use MCP\Logger\Service\GuzzleService;

$serializer = new XMLSerializer;
$service = new GuzzleService('http://localhost/log/endpoint', new Client);

$logger = new Logger($service, $serializer);
$logger->error("You've enjoyed all the power you've been given, haven't you?");
```

### Null Service

The Null Service, also known as a "black hole" service, will ignore all log messages. This is useful in environments or situations when you don't want any logs to be sent.

```php
use MCP\Logger\Logger;
use MCP\Logger\Service\NullService;

$service = new NullService;
$logger = new Logger($service);

$logger->error("There's nothing special about you. You're just an ordinary program.");
```

## Log Message

#### QL\MCP\Logger\MessageInterface

See [MessageInterface](src/MessageInterface.php) for the interface of a message. The `Message` object has the following properties:

```php

use QL\MCP\Logger\Message\Message;

$message = new Message('info', 'test message');

# Property                              # Context

$message->id();                         # 'id'
$message->message();                    # N/A
$message->severity();                   # N/A
$message->context();                    # N/A - extra keys no mentioned elsewhere
$message->details();                    # 'details'
$message->created();                    # 'created' (TimePoint)
$message->applicationID();              # 'applicationID'

// Server details
$message->serverEnvironment();          # 'serverEnvironment'
$message->serverIP();                   # 'serverIP'
$message->serverHostname();             # 'serverHostname'

// Request details
$message->requestMethod();              # 'requestMethod'
$message->requestURL();                 # 'requestURL'

// User details
$message->userAgent();                  # 'userAgent'
$message->userIP();                     # 'userIP'
```

Each of these may be set by adding a `key => value` pair to the data array when constructing a message, or in the context of the message when calling the logger.
