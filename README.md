# MCP Logger

This library allows developers to easily send log messages from their application to either the Splunk or 
[CORE](http://core) logging service. For more information on the CORE Logger service, review the service  
[documentation](https://itiki/index.php/Core_Logger).

## Contents

* [Installation](#installation)
* [Components](#components)
* [Using MCP Logger](#using-mcp-logger)
* [Services](#services)
* [PSR-3](#psr-3)
* [Components In Detail](#components-in-detail)
* [Contribute](#contribute)

## Installation

Run the following commands.

```bash
composer config repositories.internal-composer composer http://composer
composer require ql/mcp-logger ^2.3
```

## Components

The MCP Logger consists of 3 main components:

* [MCP\Logger\MessageInterface](#mcploggermessageinterface)
* [MCP\Logger\RendererInterface](#mcploggerrendererinterface)
* [MCP\Logger\ServiceInterface](#mcploggerserviceinterface)

To put things simply, a `Renderer` renders a `Message` that is sent by a `Service`. Additionally, several convenience 
classes are also available to make connecting the pieces easier.

*   [PSR-3 Compliant Logger](#psr-3)
*   [Message Factory](#build-a-message-with-the-messagefactory)

## Setup

```php
use GuzzleHttp\Client;
use MCP\Logger\Renderer\XmlRenderer;
use MCP\Logger\Service\Guzzle4Service;
use QL\UriTemplate\UriTemplate;
use XMLWriter;

$client = new Client;
$uri = new UriTemplate('http://sonic');

// A renderer of your choice
$renderer = new XmlRenderer(new XMLWriter);

// A service of your choice
$service = new Guzzle4Service($client, $renderer, $uri);
```

## Creating a Message

You can either create message objects manually or use the message factory.

### Manually

There are 5 required fields to create a message. By default, the standard message level is `INFO`. To send a message at 
a different level, you must provide it in the message data.

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

### Using the Factory

Alternatively, a convenience factory is provided that will allow you to pass message defaults at setup so you do not 
have to populate these fields every time a message is logged.

The factory will add `createTime`, `message`, and `level` to the message payload.

```php
use MCP\DataType\Time\Clock;
use MCP\Logger\Message\MessageFactory;

$clock = new Clock('now', 'UTC');
$factory = new MessageFactory($clock);

$message = $factory->buildMessage(MessageFactory::DEBUG, 'A debug message');
```

When using the factory, there are several ways for you to add data to a message.

```php
use MCP\DataType\IPv4Address;

// In the constructor...
$factory = new MessageFactory($clock, [
    'applicationId' => '1',
    'machineIPAddress' => new IPv4Address(0)
]);

// With a setter...
$factory->setDefaultProperty('machineName', 'ServerName');

// As context data when building the message...
$message = $factory->buildMessage(
    MessageFactory::DEBUG,
    'A debug message',
    ['userIPAddress' => new IPv4Address(0)]
);
```

Unknown fields that the core service does not understand will be automatically added to `Extended Properties` by the 
factory.

## Sending a Message

Once you have a service and a message, sending is easy.

```php
$service->send($message);
```

## Services

A number of services are available for you to select from. Not sure which one is right for your application? In general,
you should follow these guidelines when selecting a service.

1.  When your application is running on the Quicken Loans network, your log messages should be sent to the CORE logging
    service using the `HttpService` service. The `Guzzle3Service`, `Guzzle4Service`, and `Guzzle5Service` services have
    been deprecated.
2.  When your application is running in Amazon AWS, you should use the `SyslogService`. The `KinesisService` can also be
    used but is not recommended because operations cannot be completed asynchronously. 
    
If you are still unsure what service to select, contact the Web Core team for guidance.

### MCP Http Service

The MCP Http Service is the preferred service for sending messages to the CORE Logger service.

```php
use QL\MCP\Http\Pool;
use QL\MCP\Http\Client;
use MCP\Logger\Service\HttpService;
use MCP\Logger\Renderer\XMLRenderer;

// Instance of the MCP Http Client
$client = new Client(/* ... */);

// Instance of the MCP Http Pool
$pool = new Pool($client);

// Instance of the MCP Logger XML Renderer
$renderer = XMLRenderer();

$service = new HttpService($pool, $renderer, [
    HttpService::HOSTNAME => 'replaceme'
]);
```

The Http Service can accept a number of configuration keys and values.

*   `HttpClient::CONFIG_HOSTNAME` (required)

    The hostname to use when sending messages.

*   `HttpClient::CONFIG_SILENT` (default: `true`)

    Whether to silently fail or not. When set to false, exceptions will be thrown when an error occurs.

*   `HttpClient::CONFIG_BUFFER_LIMIT` (default: `0`)

    The number of messages to buffer before sending.

*   `HttpClient::CONFIG_SHUTDOWN` (default: `true`)

    Whether to register the shutdown handler or not. When set to true, the `flush()` method will be called when PHP is
    shutting down so that you don't need to call it manually to clear the queue of messages.

*   `HttpClient::CONFIG_TEMPLATE` (default: `{scheme}://{hostname}:{port}{/root}/{+resource}`)

    The URI Template to use when sending messages. This can be a string or `QL\UriTemplate\UriTemplate` object.

*   `HttpClient::CONFIG_SCHEME` (default: `http`)

    The scheme to use when sending messages.

*   `HttpClient::CONFIG_PORT` (default: `2581`)

    The port to use when sending messages.

*   `HttpClient::CONFIG_ROOT` (default: `web/core`)

    The root URI path to use when sending messages.

*   `HttpClient::CONFIG_RESOURCE` (default: `logentries`)

    The URI resource to use when sending messages.

### Guzzle Services

**Deprecated** Use of these services has been deprecated. Please use the MCP Http Service instead.

These services send messages to the CORE Logger service and can only be used when your application is being run on the
Quicken Loans network.

*   `Guzzle3Service`
*   `Guzzle4Service`
*   `Guzzle5Service`

When sending to the CORE Logger service, you should use the `XmlRenderer` renderer class.

### Amazon AWS Services

These services use Amazon AWS infrastructure to send messages so they can only be used when your application is being 
run on an Amazon AWS EC2 instance.

#### Kinesis Service

This service sends messages using the Amazon Kinesis service. Because of this, the Amazon AWS PHP SDK composer package
is required (`"aws/aws-sdk-php": "^3.0"`). You should also use the `JsonRenderer` to ensure that logs are formatted
correctly for Splunk.

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

    The Amazon AWS Region that you code will be deployed to. If you are unsure of this value, speak with your friendly 
    neighborhood Unix administrator.

- `version` (required)

    The Amazon AWS Kinesis API version to use when communicating. At the moment, this library supports the `2013-12-02` 
    release. Do not change this to `latest` or any more recent release without speaking with the library maintainer 
    first as it could break the sending of log messages.
    
- `credentials`

    This configuration value allows you to manually specify the credentials to use when communicating with the Amazon 
    AWS Kinesis API. You probably won't need to provide this as an IAM Role will likely be used to provide credentials. 
    For more details on how credentials work, review the [AWS SDK Credentials  Documentation](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/credentials.html) 
    or speak with a Unix administrator.
    
For more details on the available configuration values, review the [AWS SDK Configuration  Documentation](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html).

Now, just instantiate an instance of the `MCP\Logger\Service\KinesisService`.

```php
use MCP\Logger\Service\KinesisService;
use MCP\Logger\Renderer\JsonRenderer;

$service = new KinesisService(
    $client,                    // The Amazon AWS Kinesis Client
    $renderer,                  // The JSON Message Renderer
    $configuration = []         // A dictionary of configuration keys and values
);
```

The service allows a number of configuration keys to be provided, depending on your needs.

Key                                        | Type   | Required      | Explanation
------------------------------------------ | ------ | ------------- | ------------------------------------------
`KinesisService::CONFIG_IS_SILENT`         | bool   | no (`true`)   | When true, errors will be handled silently. If false, exceptions will be thrown instead.
`KinesisService::CONFIG_BUFFER_LIMIT`      | int    | no (`0`)      | Defines the maximum number of log messages that will be queued before they are sent. When set to `0`, messages are immediately sent. This value must be between 0 and 499.
`KinesisService::CONFIG_KINESIS_ATTEMPTS`  | int    | no (`5`)      | The number of attempts to make when sending log messages to Kinesis. This value must be at least 1. Because of the nature of Kinesis, it is likely that not all messages will be able to be sent on the first attempt. For this reason, it is suggested that this value be at least 5. The lower this value, the higher the liklihood that messages will be lost.
`KinesisService::CONFIG_KINESIS_STREAM`    | string | no (`Logger`) | The name of the Kinesis stream that log messages should be sent to. If you are unsure of this value, then speak with Security or a Unix administrator.
`KinesisService::CONFIG_REGISTER_SHUTDOWN` | bool   | no (`true`)   | When using a buffer limit greater than zero, you must flush queued messages (`$service->flush()`) before your application shuts down to ensure that messages are not lost. When this value is true, that flushing is done automatically.

### Generic Services

These services are generic and can be used pretty much anywhere. However, they may require that the server your 
application is being run on be configured by the Unix team first.

#### Syslog Service

This services allows you to send log messages directly to Syslog. From there, messages can be sent to a file, central
Syslog server, or to Splunk directly. These destinations must be configured by Unix administrators.

```php
use MCP\Logger\Service\SyslogService;
use MCP\Logger\Renderer\JsonRenderer;

// A renderer instance
$renderer = new JsonRenderer();

// An optional backup service where messages should be written if syslog is not accessible
$backup = null;

// An array of configuration data
$configuration = [];

$service = new SyslogService($renderer, $configuration, $backup);
```

The service allows a number of configuration keys to be provided, depending on your needs.

Key                               | Type   | Required      | Explanation
--------------------------------- | ------ | ------------- | ------------------------------------------
`SyslogService::CONFIG_SILENT`    | bool   | no (`true`)   | When true, errors will be handled silently. If false, exceptions will be thrown instead.
`SyslogService::CONFIG_IDENT`     | string | no            | See PHP `openlog()` [documentation](http://php.net/manual/en/function.openlog.php).
`SyslogService::CONFIG_FACILITY`  | string | no            | See PHP `openlog()` [documentation](http://php.net/manual/en/function.openlog.php).
`SyslogService::CONFIG_OPTIONS`   | int    | no            | See PHP `openlog()` [documentation](http://php.net/manual/en/function.openlog.php).

## PSR-3

If your application does not require a complex logging setup (e.g., cascading loggers), and is compatible with PSR-3, 
a PSR-3 Logger is provided. This logger has the Service and MessageFactory as dependencies. The logger uses a different 
MessageFactory that specifically converts a PSR-3 log level to a core log level.

**Note**: You must still provide the required message properties to the factory.

```php
use MCP\DataType\IPv4Address;
use MCP\Logger\Adapter\Psr\MessageFactory;
use MCP\Logger\Service\HttpService;
use MCP\Logger\Logger;
use XMLWriter;

$service = new HttpService(/* ... */);

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
* [Guzzle3Service.php](src/Service/Guzzle3Service.php) (deprecated)
* [Guzzle4Service.php](src/Service/Guzzle4Service.php) (deprecated)
* [Guzzle5Service.php](src/Service/Guzzle5Service.php) (deprecated)
* [HttpService.php](src/Service/HttpService.php)
* [KinesisService.php](src/Service/KinesisService.php)

##### Batched, asynchronous requests

The Http Service and Guzzle 5 Service support batched and asynchronously sending of log messages.

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
