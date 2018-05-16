<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\Exception;
use ReflectionClass;

class GuzzleServiceTest extends TestCase
{
    public function testServiceSuccessfullySendsMessage()
    {
        $client = new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(200)
        ]))]);

        $service = new GuzzleService('http://localhost/mcp/logger', $client);

        $actual = $service->send('info', 'this is a test message');
        $this->assertSame(true, $actual);
    }

    public function testServiceReceivesNon200ResponseReturnsFalse()
    {
        $client = new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(500)
        ]))]);

        $service = new GuzzleService('http://localhost/mcp/logger', $client);

        $actual = $service->send('info', 'this is a test message');
        $this->assertSame(false, $actual);
    }

    public function testMultipleErrors()
    {
        $client = new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(500),
            new Response(400)
        ]))]);

        $service = new GuzzleService('http://localhost/mcp/logger', $client);

        $actual = $service->send('info', 'this is a alpha message');
        $this->assertSame(false, $actual);

        $actual = $service->send('info', 'this is a beta message');
        $this->assertSame(false, $actual);
    }

    public function testServiceReceivesNon200ResponseSilentlyContinues()
    {
        $client = new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(500),
            new Response(400),
            new Response(503),
            new Response(200),
            new Response(200),
        ]))]);

        $service = new GuzzleService('http://localhost/mcp/logger', $client);

        $actual1 = $service->send('info', 'this is a alpha message');
        $actual2 = $service->send('info', 'this is a beta message');
        $actual3 = $service->send('info', 'this is a delta message');
        $actual4 = $service->send('info', 'this is a echo message');
        $actual5 = $service->send('info', 'this is a gamma message');

        $this->assertSame(false, $actual1);
        $this->assertSame(false, $actual2);
        $this->assertSame(false, $actual3);
        $this->assertSame(true, $actual4);
        $this->assertSame(true, $actual5);
    }


    public function testInvalidConfigurationThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid logger endpoint provided. Please provide a complete HTTP or HTTPS URL endpoint.');

        $service = new GuzzleService('xxx');
    }

    public function testEndpointInConfigurationIsValid()
    {
        $service = new GuzzleService('http://derp');

        $reflected = new ReflectionClass($service);

        $config = $reflected->getProperty('endpoint');
        $config->setAccessible(true);

        $this->assertSame('http://derp', $config->getValue($service));
    }
}
