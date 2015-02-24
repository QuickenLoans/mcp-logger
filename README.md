# Core Logger, stand-alone.

MCP-Logger is a module that lets an application log entries to our internal [Core Logger](http://core) service.

Using this package, an application can write warnings, fatals, and stack traces to Core.

Table of Contents:
* [Installation](#installation)
* [Components](#components)
* [Using MCP Logger](#using-mcp-logger)
* [PSR-3](#psr-3)
* [Components In Detail](#components-in-detail)
* [Contribute](#contribute)

See Also:

* [Core Logger](http://core)
* [Core Logger Specifications](https://itiki/index.php/Core_Logger)

## Installation

Installation with composer is recommended. Other methods are unsupported.

Add the following to your project's `composer.json`:

```javascript
{
    "repositories": [
        {
            "type": "composer",
            "url": "http://composer/"
        }
    ],
    "require": {
        "ql/mcp-logger": "*"
    }
}
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

#### Setup

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

#### Sending a message

There are 5 required fields to create a message. By default, the standard message level is `INFO`. To send a message
at a different level, you must provide it in the message data.

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

#### Build a message with the MessageFactory

Alternatively, a convenience factory is provided that will allow you to pass message defaults at setup
so you do not have to populate these fields every time a message is logged.

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

## PSR-3

If your application does not require a complex logging setup (e.g., cascading loggers), and is compatible
with PSR-3, a PSR-3 Logger is provided.

This logger has the Service and MessageFactory as dependencies. The logger uses a different MessageFactory that
specifically converts a PSR-3 log level to a core log level.

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

The `Renderer` is not directly used by consumers of this package. The renderer provided to the
service will be invoked upon the message and format the message so it can be sent. The provided renderer
converts a message to an XML string.

```php
use MCP\Logger\Renderer\XmlRenderer;
use XMLWriter;

$renderer = new XmlRenderer(new XMLWriter);
$output = $renderer($message);
```

See also:

* [RendererInterface.php](src/RendererInterface.php)
* [XmlRenderer.php](src/Renderer/XmlRenderer.php)

#### MCP\Logger\ServiceInterface

By default, the provided Http Service silently consumes exceptions if the http request fails.

```php
use MCP\Logger\Service\HttpService;

$isSilent = false;
$service = new HttpService($request, $renderer, $isSilent);
$service->send($message);
```

See also:

* [ServiceInterface.php](src/ServiceInterface.php)
* [PeclHttpService.php](src/Service/PeclHttpService.php)
* [Guzzle3Service.php](src/Service/Guzzle3Service.php)
* [Guzzle4Service.php](src/Service/Guzzle4Service.php)
* [Guzzle5Service.php](src/Service/Guzzle5Service.php)

## Contribute

#### Standards

This library follows PSR-2 conventions.

#### Install development dependencies

```bash
bin/install
```

#### Wipe compiled and built files

```bash
bin/clean
```

#### Run unit tests

```bash
vendor/bin/phpunit

# Run unit tests
vendor/bin/phpunit --testsuite unit

# Run integration tests
vendor/bin/phpunit --testsuite integration
```
