<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\Mock;
use Mockery;
use PHPUnit_Framework_TestCase;

class Guzzle5ServiceTest extends PHPUnit_Framework_TestCase
{
    public static $logSetting;
    public $uri;
    public $renderer;

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
        $this->uri = Mockery::mock('QL\UriTemplate\UriTemplate', [
            'expand' => 'http://corelogger'
        ]);

        $this->renderer = Mockery::mock('MCP\Logger\RendererInterface', ['contentType' => 'text/xml']);

        touch(__DIR__ . '/errlog');
    }

    public function tearDown()
    {
        unlink(__DIR__ . '/errlog');
    }

    /**
     * @expectedException MCP\Logger\Exception
     * @expectedExceptionMessage Server error response [url] http://corelogger [status code] 500 [reason phrase] Internal Server Error
     */
    public function testServiceReceivesNon200ResponseThrowsException()
    {
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $mock = new Mock([
            new Response(500),
        ]);

        $client = new Client;
        $client->getEmitter()->attach($mock);

        $this->renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('rendered message')
            ->once();

        $service = new Guzzle5Service($client, $this->renderer, $this->uri, false, false);
        $service->send($message);
    }

    /**
     * @expectedException MCP\Logger\Exception
     * @expectedExceptionMessage 2 Errors occured while sending 2 messages
     */
    public function testMultipleErrorsWhenNotSilent()
    {
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $mock = new Mock([
            new Response(500),
            new Response(400),
        ]);

        $client = new Client;
        $client->getEmitter()->attach($mock);

        $this->renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('rendered message')
            ->twice();

        $service = new Guzzle5Service($client, $this->renderer, $this->uri, false, false, 1);
        $service->send($message);
        $service->send($message);

        // empty
        $this->assertSame('', file_get_contents(__DIR__ . '/errlog'));
    }

    public function testServiceReceivesNon200ResponseSilentlyContinues()
    {
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $mock = new Mock([
            new Response(500),
            new Response(400),
            new Response(503),
            new Response(200),
            new Response(200),
        ]);

        $client = new Client;
        $client->getEmitter()->attach($mock);

        $this->renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('rendered message')
            ->times(5);

        $service = new Guzzle5Service($client, $this->renderer, $this->uri, true, false, 10);
        $service->send($message);
        $service->send($message);
        $service->send($message);
        $service->send($message);
        $service->send($message);

        $service->flush();

        $this->assertContains('3 Errors occured while sending 5 messages', file_get_contents(__DIR__ . '/errlog'));
    }

    public function testSilentLoggingDoesNotUseIndividualErrorMessage()
    {
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $mock = new Mock([
            new Response(500),
            new Response(200),
            new Response(200),
        ]);

        $client = new Client;
        $client->getEmitter()->attach($mock);

        $this->renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('rendered message')
            ->times(3);

        $service = new Guzzle5Service($client, $this->renderer, $this->uri, true, false, 0);
        $service->send($message);
        $service->send($message);
        $service->send($message);

        $service->flush();

        $this->assertContains('1 Errors occured while sending 1 messages with mcp-logger', file_get_contents(__DIR__ . '/errlog'));
    }
}
