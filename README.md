# MCP Logger

[![Build Status](https://travis-ci.org/quickenloans-mcp/mcp-logger.png)](https://travis-ci.org/quickenloans-mcp/mcp-logger)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/quickenloans-mcp/mcp-logger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/quickenloans-mcp/mcp-logger/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/ql/mcp-logger/version)](https://packagist.org/packages/ql/mcp-logger)
[![License](https://poser.pugx.org/ql/mcp-logger/license)](https://packagist.org/packages/ql/mcp-logger)

Structured logging for Quicken Loans PHP projects.

We prefer structured log messages defined on top of [PSR-3 Logger Interface](http://www.php-fig.org/psr/psr-3/)
to allow easier searching of log messages from our log provider.

This library shares some features with the popular PHP logging library **Monolog**,
but we aim to keep this library small and simple and avoid unnecessary dependencies for our projects.

## Contents

- [Installation](#installation)
- [Components](#components)
- [Usage](#usage)
    - [Filter Logger](#filter-logger)
    - [Broadcast Logger](#broadcast-logger)
    - [Creating a Message](#creating-a-message)
    - [Sending a Message](#sending-a-message)
- [Services](#services)
    - [Syslog Service](#syslog-service) **RECOMMENDED LOGGING METHOD**
    - [Guzzle Service](#guzzle-service) (For Guzzle 5 and 6)
    - [ErrorLog Service](#errorlog-service) (Send messages to log file - for development)
    - [Null Service](#null-service) (To ignore messages)
- [Components In Detail](#components-in-detail)

## Installation

Run the following commands.

```bash
composer require ql/mcp-logger ~3.0
```

### Usage with Symfony

A `mcp-logger.yml` configuration file for symfony config/di is included with this library. This allows for
easily including mcp logger components and using them within your app.

Add the following somewhere to your symfony DI yml configuration:
```yaml
imports:
    - resource: ../vendor/ql/mcp-logger/configuration/mcp-logger.yml
```

Then you can use the following services throughout your DI:

- `@mcp.logger` - PSR-3 logger
    - By default this uses the **Syslog** service.
- `@mcp.logger.factory` - Message factory
    - Append additional default parameters to configure the factory.
- `@mcp.logger.service` - Service used by `mcp.logger`.

See [configuration/mcp-logger.yml](configuration/mcp-logger.yml) for all the services and parameters available.

#### Configure logger through the following parameters:

- `%mcp.logger.default_properties%` - Default message properties
   > Example:
   > ```yaml
   > parameters:
   >     mcp.logger.default_properties:
   >        applicationID: 12345
   >        serverEnvironment: staging
   > ```

- `%mcp.logger.service.syslog.options%` - Customize Syslog Service
   > Example:
   > ```yaml
   > parameters:
   >     mcp.logger.service.syslog.options:
   >        ident: mytestapp
   >        facility: 144 # LOG_LOCAL2
   > ```

- `%mcp.logger.service.errorlog.options%` - Customize ErrorLog Service
- `%mcp.logger.service.guzzle.options%` - Customize Guzzle Service


#### Changing logger service

To change the service used by the logger (if you do not want to use syslog) simply change the main service.
```yaml
services:
    mcp.logger.service:
        parent: 'mcp.logger.service.errorlog'
```

## Components

The MCP Logger consists of three main components:

- [MCP\Logger\MessageInterface](#mcploggermessageinterface)
- [MCP\Logger\Service\SerializerInterface](#mcploggerserviceserializerinterface)
- [MCP\Logger\ServiceInterface](#mcploggerserviceinterface)

To put things simply, a `Serializer` serializes a `Message` that is sent by a `Service`.
Additionally, several convenience classes are also available to make connecting the pieces easier.

## Usage

- [Filter Logger](#filter-logger)
- [Broadcast Logger](#broadcast-logger)
- [Creating a Message](#creating-a-message)
- [Sending a Message](#sending-a-message)

```php
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\Service\Serializer\JSONSerializer;
use QL\MCP\Logger\Service\SyslogService;
use QL\MCP\Logger\Logger;

// Basic setup, uses Syslog with no ident by default.
$logger = new Logger;

// To customize your options, build the factory and services and inject into the Logger.
$service = new SyslogService(new JSONSerializer, [
    SyslogService::CONFIG_IDENT => 'mytestapp'
]);
$factory = new MessageFactory;
$factory->setDefaultProperty('applicationID', '123456');
$factory->setDefaultProperty('serverHostname', 'mydevbox');
$factory->setDefaultProperty('serverEnvironment', 'staging');

$loggerCustom = new Logger($service, $factory);

// Send psr-3 logs
$logger->info('Hello World!');

$loggerCustom->error('Hello Major Tom, are you receiving me?', [
    'errCode' => 42
]);
```

### Filter Logger

If you want to set a minimum logging severity and only log message that meet or exceed that level, you may also
want to use the `FilterLogger` class. This wraps the main logger and provides that filtering functionality.

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

This is useful for changing the type of messages you want logged between environments or using runtime configuration to
change the level of detail in your logs on the fly.

### Broadcast Logger

The `BroadcastLogger` class allows for broadcasting a single log message out to multiple logger services. Combined with
the [Filter Logger](#filter-logger), this can allow for sending high priority messages to an on-call alerting service,
and lower priority messages such as `debug` or `info` to a lower priority alerting service.

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

### Creating a Message

You can either create message objects manually or use the message factory.

#### Manually

There are 5 required fields to create a message. By default, the standard message severity is `LogLevel::INFO`.
To send a message at a different level, you must provide it in the message data.

```php
use Psr\Log\LogLevel;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\TimePoint;
use QL\MCP\Logger\Message\Message;

$message = new Message([
    Message::SEVERITY => LogLevel::NOTICE,
    Message::MESSAGE => 'This is a message',
    Message::CREATED => new TimePoint(2013, 8, 15, 0, 0, 0, 'UTC'),

    Message::APPLICATION_ID => '1234',
    Message::SERVER_IP => IPv4Address::create('127.0.0.1'),
    Message::SERVER_HOSTNAME => 'mydevbox'
]);

// Send a message
$service->send($message);
```

#### Using the Factory

Alternatively, a convenience factory is provided that will allow you to pass message defaults at setup
so you do not have to populate these fields every time a message is logged.

The factory will add `created`, `message`, and `severity` to the message payload.

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
$factory = new MessageFactory(null, [
    'applicationID' => '1',
    'serverIP' => new IPv4Address(0)
]);

// With a setter
$factory->setDefaultProperty('serverHostname', 'ServerName');

// As context data when building the message
$message = $factory->buildMessage(LogLevel::DEBUG, 'A debug message', [
    'userIP' => new IPv4Address(0)
]);
```

Unknown context that are not part of the standard properties of `MessageInterface` will be automatically added
to `context` by the factory.

### Sending a Message

Once you have a service and a message, sending is easy.

```php
use QL\MCP\Logger\Service\ErrorLogService;
use QL\MCP\Logger\Service\Serializer\LineSerializer;

$service = new ErrorLogService(new LineSerializer, [
    ErrorLogService::CONFIG_TYPE => ErrorLogService::FILE,
    ErrorLogService::CONFIG_FILE => '/var/log/myappmessages'
]);

$service->send($message);
```

## Services

A number of services are available. In general use the services in the following scenarios:

- Actively maintained and supported (high performance)
    - [Syslog Service](#syslog-service) **RECOMMENDED LOGGING METHOD** (Sends logs to server syslog)
- In local developer environment:
    - [ErrorLog Service](#errorlog-service) (Send messages to log file)
    - [Null Service](#null-service) (Ignore messages)
- Legacy application (performance not important):
    - [Guzzle Service](#guzzle-service) (Send message over HTTP with Guzzle 5 or 6)

#### Syslog Service

This service allows you to send log messages directly to Syslog. From there, messages can be sent to a file, central
Syslog server, or to a log collector such as Splunk or LogEntries. These destinations must be configured by
system administrators.

This service should only use `JSONSerializer` or `LineSerializer`.

```php
use MCP\Logger\Logger;
use MCP\Logger\Service\SyslogService;
use MCP\Logger\Service\Serializer\JSONSerializer;

$serializer = new JSONSerializer;
$service = new SyslogService($serializer, [
    // Ident SHOULD always be provided, to allow log filtering.
    SyslogService::CONFIG_IDENT => 'MyAppName'
]);

$logger = new Logger($service);

$logger->error("That MCP, that's half our problem right there.");
```

The service allows a number of configuration keys to be provided, depending on your needs.

Property                          | Type   | Default                 | Explanation
--------------------------------- | ------ | ----------------------- | ------------------------------------------
`SyslogService::CONFIG_SILENT`    | bool   | `true`                  | When true, errors will be handled silently. If false, exceptions will be thrown instead.
`SyslogService::CONFIG_IDENT`     | string | blank                   | See php.net `openlog()` [documentation](http://php.net/manual/en/function.openlog.php).
`SyslogService::CONFIG_FACILITY`  | string | `LOG_USER`              | See php.net `openlog()` [documentation](http://php.net/manual/en/function.openlog.php).
`SyslogService::CONFIG_OPTIONS`   | int    | `LOG_ODELAY | LOG_CONS` | See php.net `openlog()` [documentation](http://php.net/manual/en/function.openlog.php).

### Guzzle Service

This service sends log message over HTTP with Guzzle. Guzzle 5 and 6 are currently supported.

This service should only use `XMLSerializer`.

```php
use GuzzleHttp\Client;
use MCP\Logger\Service\Serializer\XMLSerializer;
use MCP\Logger\Service\GuzzleService;

$service = new GuzzleService('http://localhost/log/endpoint', new Client, new XMLSerializer, [
    GuzzleService::CONFIG_TIMEOUT => 2
]);

$logger = new Logger($service);
$logger->error("You've enjoyed all the power you've been given, haven't you?");
```

The service allows a number of configuration keys to be provided, depending on your needs.

Property                                | Type   | Default     | Explanation
--------------------------------------- | ------ | ----------- | ------------------------------------------
`GuzzleService::CONFIG_SILENT`          | bool   | `true`      | When true, errors will be handled silently. If false, exceptions will be thrown instead.
`GuzzleService::CONFIG_TIMEOUT`         | int    | `2`         | Timeout in seconds for sending a message.
`GuzzleService::CONFIG_CONNECT_TIMEOUT` | int    | `1`         | Timeout in seconds for connecting to log service.
`GuzzleService::CONFIG_ENDPOINT`        | string | blank       | Endpoint for log service. May be provided in configuration, or constructor.

### ErrorLog Service

This service sends log messages through the php function `error_log()` [documentation](http://php.net/manual/en/function.error-log.php).
ErrorLog easily provides the ability to send messages to the OS, SAPI (such as NGINX), or a file.

This service should only use `JSONSerializer` or `LineSerializer`.

```php
use MCP\Logger\Service\Serializer\LineSerializer;
use MCP\Logger\Service\ErrorLogService;

$service = new ErrorLogService(new LineSerializer, [
    ErrorLogService::CONFIG_TYPE => ErrorLogService::FILE
    ErrorLogService::CONFIG_FILE => '/var/log/mcplogger'
]);

$logger = new Logger($service);
$logger->error('The MCP is the most efficient way of handling what we do!');
```

The service allows a number of configuration keys to be provided, depending on your needs.

Property                         | Type   | Default            | Explanation
-------------------------------- | ------ | ------------------ | ------------------------------------------
`ErrorLogService::CONFIG_FILE`   | string | blank              | Only required if `CONFIG_TYPE` of `ErrorLogService::FILE` is used.
`ErrorLogService::CONFIG_TYPE`   | int    | `OPERATING_SYSTEM` | See php.net `error_log()` [documentation](http://php.net/manual/en/function.error-log.php).

### Null Service

The Null Service, also known as a "black hole" service, will ignore all log messages. This is useful in environments
or situations when you don't want any logs to be sent.

```php
use MCP\Logger\Logger;
use MCP\Logger\Service\NullService;

$service = new NullService;
$logger = new Logger($service);

$logger->error("There's nothing special about you. You're just an ordinary program.");
```

## Components In Detail

#### MCP\Logger\MessageInterface

The `Message` object has the following properties:

```php
$message->id();
$message->message();
$message->severity();
$message->context();
$message->errorDetails();
$message->created();
$message->applicationID();

// Server details
$message->serverEnvironment();
$message->serverIP();
$message->serverHostname();

// Request details
$message->requestMethod();
$message->requestURL();

// User details
$message->userAgent();
$message->userIP();
$message->userName();
```
Each of these may be set by adding a `key => value` pair to the data array when constructing a message.

The following properties are required:

- `message`
- `created`
- `applicationID`
- `serverIP`
- `serverHostname`

The following properties are required but will populate with defaults if missing:

- `id` (default: random GUID)
- `severity` (default: `info`)
- `context` (default: `[]`)

See also:

- [MessageInterface.php](src/MessageInterface.php)
- [Message.php](src/Message/Message.php)

#### MCP\Logger\Service\SerializerInterface

The `Serializer` is not directly used by consumers of this package. The serializer provided to the service
will be invoked upon the message and format the message so it can be sent.

```php
use MCP\Logger\Service\Serializer\JSONSerializer;

$serializer = new JSONSerializer;
$output = $serializer($message);
```

See also:

- [SerializerInterface.php](src/Service/SerializerInterface.php)
- [JSONSerializer.php](src/Service/Serializer/JSONSerializer.php)
- [LineSerializer.php](src/Service/Serializer/LineSerializer.php)
- [XMLSerializer.php](src/Service/Serializer/XMLSerializer.php)

#### MCP\Logger\ServiceInterface

```php
use MCP\Logger\Service\Serializer\JSONSerializer;
use MCP\Logger\Service\SyslogService;

$serializer = new JSONSerializer;
$service = new SyslogService($serializer, [
    SyslogService::CONFIG_IDENT => 'MyAppName'
]);

$service->send($message);
```

See also:

- [ServiceInterface.php](src/ServiceInterface.php)
- [SyslogService.php](src/Service/SyslogService.php)
- [ErrorLogService.php](src/Service/ErrorLogService.php)
- [GuzzleService.php](src/Service/GuzzleService.php)
