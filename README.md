# MCP Logger

MCP Logger is a module that lets an PHP applications log messages.

The primary service for logging is our internal [Core Logger](http://core) service.

Using this package, an application can write warnings, fatals, and stack traces to Core.

Table of Contents:
* [Installation](#installation)
* [Components](#components)
* [Using MCP Logger](#using-mcp-logger)
* [Using MCP Logger in AWS](#using-mcp-logger-in-aws)
* [PSR-3](#psr-3)
* [Components In Detail](#components-in-detail)
* [Contribute](#contribute)

See Also:

* [Core Logger](http://core)
* [Core Logger Specifications](https://itiki/index.php/Core_Logger)

## Installation

[[?](http://composer/)] Run the following command.

```
composer require ql/mcp-logger 2.*
```

## Components

The MCP Logger consists of 3 main components:

* [MCP\Logger\MessageInterface](#mcploggermessageinterface)
* [MCP\Logger\RendererInterface](#mcploggerrendererinterface)
* [MCP\Logger\ServiceInterface](#mcploggerserviceinterface)

**In a single sentence:**  
The `Renderer` renders a `Message` that is sent by the `Service`.

In addition there are several convenience classes:

A PSR-3 Logger:

* [MCP\Logger\Logger](#psr-3)

A Message Factory

* [MCP\Logger\Message\MessageFactory](#build-a-message-with-the-messagefactory)

## Using MCP Logger

### Setup

```php
use GuzzleHttp\Client;
use MCP\Logger\Renderer\XmlRenderer;
use MCP\Logger\Service\Guzzle4Service;
use QL\UriTemplate\UriTemplate;
use XMLWriter;

$renderer = new XmlRenderer(new XMLWriter);
$client = new Client;
$uri = new UriTemplate('http://sonic');
$service = new Guzzle4Service($client, $renderer, $uri);
```

### Sending a message

There are 5 required fields to create a message. By default, the standard message level is `INFO`. To send a message at a different level, you must provide it in the message data.

```php
use MCP\DataType\IPv4Address;
use MCP\DataType\Time\TimePoint;
use MCP\Logger\Message\Message;

$message = new Message([
    'applicationId' => '1',
    'createTime' => new TimePoint(2013, 8, 15, 0, 0, 0, 'UTC'),
    'machineIPAddress' => new IPv4Address(0),
    'machineName' => 'ServerName',
    'message' => 'This is a message'
]);

// Send a message
$service->send($message);
```

### Build a message with the MessageFactory

Alternatively, a convenience factory is provided that will allow you to pass message defaults at setup so you do not have to populate these fields every time a message is logged.

The factory will add `createTime`, `message`, and `level` to the message payload.
```php
use MCP\DataType\Time\Clock;
use MCP\Logger\Message\MessageFactory;

$clock = new Clock('now', 'UTC');
$factory = new MessageFactory($clock);

$message = $factory->buildMessage(MessageFactory::DEBUG, 'A debug message');
```

There are three ways to add data to a message when using the factory.

In the constructor:
```php
use MCP\DataType\IPv4Address;

$factory = new MessageFactory($clock, [
    'applicationId' => '1',
    'machineIPAddress' => new IPv4Address(0)
]);
```

With a setter
```php
$factory->setDefaultProperty('machineName', 'ServerName');
```

As context data when building the message:
```php
use MCP\DataType\IPv4Address;

$message = $factory->buildMessage(
    MessageFactory::DEBUG,
    'A debug message',
    ['userIPAddress' => new IPv4Address(0)]
);
```

Unknown fields that the core service does not understand will be automatically added to `Extended Properties`
by the factory.

## Using MCP Logger in AWS

When using MCP Logger on Amazon AWS (the cloud), there are a few differences you should be aware of.

- Messages are sent through an Amazon Kinesis channel.
- Messages are received and processed by Splunk instead of CORE Logger.
- You must use the `MCP\Logger\Service\KinesisService` logger service.
- The Amazon AWS PHP SDK composer package is required ("aws/aws-sdk-php": "^3.0").

To get started with the Kinesis Service, you need an instance of the AWS Kinesis Client.

```php
usw Aws\Kinesis\KinesisClient;

$client = new KinesisClient([
    'region' => 'replaceme',        // Amazon AWS Region (required)
    'version' => '2013-12-02',      // Amazon AWS Kinesis API Version (required)
    'credentials' => [              // Amazon AWS Credentials
        'key' => 'replaceme',
        'secret' => 'replaceme'
    ]
]);
```

In the above example, you'll need to provide the following configuration keys.

- `region` (required)

    The Amazon AWS Region that you code will be deployed to. If you are unsure of this value, speak with your friendly neighborhood Unix administrator.

- `version` (required)

    The Amazon AWS Kinesis API version to use when communicating. At the moment, this library supports the `2013-12-02` release. Do not change this to `latest` or any more recent release without speaking with the library maintainer first as it could break the sending of log messages.
    
- `credentials`

    This configuration value allows you to manually specify the credentials to use when communicating with the Amazon AWS Kinesis API. You probably won't need to provide this as an IAM Role will likely be used to provide credentials. For more details on how credentials work, review the [AWS SDK Credentials  Documentation](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/credentials.html) or speak with a Unix administrator.
    
For more details on the available configuration values, review the [AWS SDK Configuration  Documentation](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html).

@todo

## PSR-3

If your application does not require a complex logging setup (e.g., cascading loggers), and is compatible with PSR-3, a PSR-3 Logger is provided.

This logger has the Service and MessageFactory as dependencies. The logger uses a different MessageFactory that specifically converts a PSR-3 log level to a core log level.

**Note**: You must still provide the required message properties to the factory.

### In use

```php
use GuzzleHttp\Client;
use MCP\DataType\IPv4Address;
use MCP\Logger\Adapter\Psr\MessageFactory;
use MCP\Logger\Logger;
use MCP\Logger\Renderer\XmlRenderer;
use MCP\Logger\Service\Guzzle5Service;
use XMLWriter;

$renderer = new XmlRenderer(new XMLWriter);
$client = new Client;
$uri = new UriTemplate('http://sonic');
$service = new Guzzle5Service($client, $renderer, $uri);

$clock = new Clock('now', 'UTC');
$factory = new MessageFactory($clock);
$logger = new Logger($service, $factory);

// Do not forget to add the required properties!
$factory->setDefaultProperty('applicationId', 1);
$factory->setDefaultProperty('machineIPAddress', new IPv4Address(0));
$factory->setDefaultProperty('machineName', 'ServerName');

// Log an error
$logger->error('Error Message!');

// Log a warning
$context = ['exceptionData' => 'stacktrace dump here'];
$logger->warning('Warning Message!', $context);
```

## Components In Detail

#### MCP\Logger\MessageInterface

The `Message` object has the following properties:

```php
$message->affectedSystem();
$message->applicationId();
$message->categoryId();
$message->createTime();
$message->exceptionData();
$message->extendedProperties();
$message->isUserDisrupted();
$message->level();
$message->loanNumber();
$message->machineIPAddress();
$message->machineName();
$message->message();
$message->referrer();
$message->requestMethod();
$message->url();
$message->userAgentBrowser();
$message->userCommonId();
$message->userDisplayName();
$message->userIPAddress();
$message->userName();
$message->userScreenName();
```
Each of these may be set by adding a `key => value` pair to the data array when constructing a message.

The following properties are required:

* applicationId
* createTime
* machineIPAddress
* machineName
* message

The following properties are required but will populate with defaults if missing:

* extendedProperties
* level
* isUserDisrupted

See also:

* [MessageInterface.php](src/MessageInterface.php)
* [Message.php](src/Message/Message.php)
* [Core Logger Specifications](https://itiki/index.php/Core_Logger)

#### MCP\Logger\RendererInterface

The `Renderer` is not directly used by consumers of this package. The renderer provided to the service will be invoked upon the message and format the message so it can be sent.

```php
use MCP\Logger\Renderer\XmlRenderer;
use XMLWriter;

$renderer = new XmlRenderer(new XMLWriter);
$output = $renderer($message);
```

See also:

* [RendererInterface.php](src/RendererInterface.php)
* [JsonRenderer.php](src/Renderer/JsonRenderer.php)
* [XmlRenderer.php](src/Renderer/XmlRenderer.php)

#### MCP\Logger\ServiceInterface

By default, the provided Http Services silently consumes exceptions if the http request fails.

```php
use GuzzleHttp\Client;
use MCP\Logger\Service\Guzzle4Service;
use QL\UriTemplate\UriTemplate;

$isSilent = true;

$service = new Guzzle4Service(new Client, $renderer, new UriTemplate('http://corelogger'), $isSilent);
$service->send($message);
```

See also:

* [ServiceInterface.php](src/ServiceInterface.php)
* [Guzzle3Service.php](src/Service/Guzzle3Service.php)
* [Guzzle4Service.php](src/Service/Guzzle4Service.php)
* [Guzzle5Service.php](src/Service/Guzzle5Service.php)
* [KinesisService.php](src/Service/KinesisService.php)

##### Batched, asynchronous requests

Use Guzzle 5 and the included Guzzle 5 service to batch log messages, and send them asychronously in groups.

**Note**: The Guzzle3, and Guzzle4 services do not support batching or asynchronous requests.

By default, this service will **not** buffer any messages, and immediately send new messages.

Increase the `$batchLimit` to group messages. Messages will be sent once the batch limit is reached, or at the end of the entire PHP request.

You can disable the **shutdown handler** if you wish to flush messages manually. In addition, if you have an error handler that catches fatal errors, this service must be instantiated **after** the error handler is attached so that messages logged in the error handler will be sent by the logger.

```php
use GuzzleHttp\Client;
use MCP\Logger\Service\Guzzle5Service;
use QL\UriTemplate\UriTemplate;

$isSilent = true;
$useShutDownHandler = false;
$batchLimit = 5;

$service = new Guzzle5Service(new Client, $renderer, new UriTemplate('http://corelogger'), $isSilent, $useShutDownHandler, $batchLimit);
$service->send($message);
$service->send($message);
$service->send($message);

// Manually flush the messages queued, since the shutdown handler was disabled.
$service->flush();
```

## Contribute

#### Standards

This library follows PSR-2 conventions.

#### Install dependencies

```bash
composer install --prefer-dist
```

#### Run unit tests

```bash
# Run unit tests
vendor/bin/phpunit

# Run integration tests
vendor/bin/phpunit --group integration
```
