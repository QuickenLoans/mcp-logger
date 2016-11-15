<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\Mock;
use Mockery;
use PHPUnit_Framework_TestCase;
use QL\MCP\Logger\Exception;
use QL\MCP\Logger\MessageInterface;
use QL\MCP\Logger\Service\Serializer\XMLSerializer;
use ReflectionClass;

class GuzzleServiceTest extends PHPUnit_Framework_TestCase
{
    public static $logSetting;
    public $uri;
    public $serializer;

    public static function setUpBeforeClass()
    {
        self::$logSetting = ini_get('error_log');
        ini_set('error_log', __DIR__ . '/errlog');
    }

    public static function tearDownAfterClass()
    {
        ini_set('error_log', self::$logSetting);
    }

    public function setUp()
    {
        $this->serializer = Mockery::mock(SerializerInterface::class, [
            'contentType' => 'text/xml'
        ]);

        $this->message = Mockery::mock(MessageInterface::class);
    }

    public function tearDown()
    {
        @unlink(__DIR__ . '/errlog');
    }

    public function testServiceSuccessfullySendsMessage()
    {
        $client = new Client;
        $client->getEmitter()->attach(new Mock([
            new Response(200),
        ]));

        $this->serializer
            ->shouldReceive('__invoke')
            ->with($this->message)
            ->once()
            ->andReturn('message');

        $service = new GuzzleService('http://localhost/mcp/logger', $client, $this->serializer, [
            GuzzleService::CONFIG_SILENT => false
        ]);

        $service->send($this->message);

        $this->assertFileNotExists(__DIR__ . '/errlog');
    }

    public function testServiceReceivesNon200ResponseThrowsExceptionWithMessage()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Server error response [url] http://localhost/mcp/logger [status code] 500 [reason phrase] Internal Server Error');

        $client = new Client;
        $client->getEmitter()->attach(new Mock([
            new Response(500),
        ]));

        $this->message
            ->shouldReceive('message')
            ->andReturn('log message text');

        $this->serializer
            ->shouldReceive('__invoke')
            ->with($this->message)
            ->once()
            ->andReturn('message');

        $service = new GuzzleService('http://localhost/mcp/logger', $client, $this->serializer, [
            GuzzleService::CONFIG_SILENT => false
        ]);

        $service->send($this->message);
    }

    public function testMultipleErrorsWhenSilent()
    {
        $client = new Client;
        $client->getEmitter()->attach(new Mock([
            new Response(500),
            new Response(400),
        ]));

        $this->message
            ->shouldReceive('message')
            ->andReturn('log message text 1', 'log message text 1', 'log message text 2')
            ->times(4);

        $this->serializer
            ->shouldReceive('__invoke')
            ->with($this->message)
            ->twice()
            ->andReturn('message');

        $service = new GuzzleService('http://localhost/mcp/logger', $client, $this->serializer, [
            GuzzleService::CONFIG_SILENT => true
        ]);

        $service->send($this->message);
        $service->send($this->message);

        $expectedLog1 = <<<LOG
MCP HTTP Logger failed : log message text 1 (Server error response [url] http://localhost/mcp/logger [status code] 500 [reason phrase] Internal Server Error)
LOG;
        $expectedLog2 = <<<LOG
MCP HTTP Logger failed : log message text 2 (Client error response [url] http://localhost/mcp/logger [status code] 400 [reason phrase] Bad Request)
LOG;

        $actualLog = file_get_contents(__DIR__ . '/errlog');
        $this->assertContains($expectedLog1, $actualLog);
        $this->assertContains($expectedLog2, $actualLog);
    }

    public function testServiceReceivesNon200ResponseSilentlyContinues()
    {
        $client = new Client;
        $client->getEmitter()->attach(new Mock([
            new Response(500),
            new Response(400),
            new Response(503),
            new Response(200),
            new Response(200),
        ]));

        $this->message
            ->shouldReceive('message')
            ->andReturn('log message text');

        $this->serializer
            ->shouldReceive('__invoke')
            ->with($this->message)
            ->times(5)
            ->andReturn('message');

        $service = new GuzzleService('http://localhost/mcp/logger', $client, $this->serializer, [
            GuzzleService::CONFIG_SILENT => true
        ]);

        $service->send($this->message);
        $service->send($this->message);
        $service->send($this->message);
        $service->send($this->message);
        $service->send($this->message);

        $expectedLog1 = <<<LOG
MCP HTTP Logger failed : log message text (Server error response [url] http://localhost/mcp/logger [status code] 500 [reason phrase] Internal Server Error)
LOG;
        $expectedLog2 = <<<LOG
MCP HTTP Logger failed : log message text (Client error response [url] http://localhost/mcp/logger [status code] 400 [reason phrase] Bad Request)
LOG;

        $expectedLog3 = <<<LOG
MCP HTTP Logger failed : log message text (Server error response [url] http://localhost/mcp/logger [status code] 503 [reason phrase] Service Unavailable)
LOG;

        $actualLog = file_get_contents(__DIR__ . '/errlog');
        $this->assertContains($expectedLog1, $actualLog);
        $this->assertContains($expectedLog2, $actualLog);
        $this->assertContains($expectedLog3, $actualLog);
    }

    public function testLongErrorIsTruncated()
    {
        $client = new Client;
        $client->getEmitter()->attach(new Mock([
            new Response(500),
        ]));

        $this->message
            ->shouldReceive('message')
            ->andReturn("err message\n" . "on multiple lines\n");

        $this->serializer
            ->shouldReceive('__invoke')
            ->with($this->message)
            ->once();

        $service = new GuzzleService('http://localhost/mcp/logger', $client, $this->serializer, [
            GuzzleService::CONFIG_SILENT => true
        ]);

        $service->send($this->message);

        $expectedLog = <<<LOG
MCP HTTP Logger failed : err message (Server error response [url] http://localhost/mcp/logger [status code] 500 [reason phrase] Internal Server Error)
LOG;

        $actualLog = file_get_contents(__DIR__ . '/errlog');
        $this->assertContains($expectedLog, $actualLog);
    }

    public function testInvalidConfigurationThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid logger endpoint provided. Please provide a complete HTTP or HTTPS URL endpoint.');

        $service = new GuzzleService;
    }

    public function testEndpointInConfigurationIsValid()
    {
        $service = new GuzzleService(null, null, null, [
            'endpoint' => 'http://derp'
        ]);

        $reflected = new ReflectionClass($service);

        $config = $reflected->getProperty('configuration');
        $config->setAccessible(true);

        $this->assertSame('http://derp', $config->getValue($service)['endpoint']);
    }

    public function testDefaultDependencies()
    {
        $service = new GuzzleService('http://derp');

        $reflected = new ReflectionClass($service);

        $guzzle = $reflected->getProperty('guzzle');
        $guzzle->setAccessible(true);

        $serializer = $reflected->getProperty('serializer');
        $serializer->setAccessible(true);

        $this->assertInstanceOf(Client::class, $guzzle->getValue($service));
        $this->assertInstanceOf(XMLSerializer::class, $serializer->getValue($service));
    }
}
