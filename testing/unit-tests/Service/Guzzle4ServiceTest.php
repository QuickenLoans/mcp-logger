<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use GuzzleHttp\Exception\RequestException;
use Mockery;
use PHPUnit_Framework_TestCase;

class Guzzle4ServiceTest extends PHPUnit_Framework_TestCase
{
    public static $logSetting;
    public $uri;

    public static function setUpBeforeClass()
    {
        self::$logSetting = ini_get('error_log');
        ini_set('error_log', 'syslog');
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
    }

    /**
     * @expectedException MCP\Logger\Exception
     * @expectedExceptionMessage The service responded with an unexpected http code: '404'
     */
    public function testServiceReceivesNon200ResponseThrowsException()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $options = [
            'body' => 'rendered message',
            'headers' => ['Content-Type' => 'text/xml']
        ];

        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $client
            ->shouldReceive('post')
            ->with('http://corelogger', $options)
            ->andReturn(Mockery::mock('GuzzleHttp\Message\Response', ['getStatusCode' => '404']))
            ->once();

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('rendered message')
            ->once();

        $service = new Guzzle4Service($client, $renderer, $this->uri);
        $service->send($message);
    }

    /**
     * @expectedException GuzzleHttp\Exception\RequestException
     * @expectedExceptionMessage msg
     */
    public function testExceptionIsNotCaughtWhenServiceThrowsHttpException()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $client
            ->shouldReceive('post')
            ->andThrow(new RequestException('msg', Mockery::mock('GuzzleHttp\Message\RequestInterface')));

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('rendered message');

        $service = new Guzzle4Service($client, $renderer, $this->uri);
        $service->send($message);
    }

    public function testServiceReceivesNon200ResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $client
            ->shouldReceive('post')
            ->andReturn(Mockery::mock('GuzzleHttp\Message\Response', ['getStatusCode' => '404']))
            ->once();

        $renderer
            ->shouldReceive('__invoke')
            ->once();

        $service = new Guzzle4Service($client, $renderer, $this->uri, true);
        $this->assertNull($service->send($message));
    }

    public function testServiceThrowsHttpExceptionResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');


        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $client
            ->shouldReceive('post')
            ->andThrow(new RequestException('msg', Mockery::mock('GuzzleHttp\Message\RequestInterface')));

        $renderer
            ->shouldReceive('__invoke')
            ->once();

        $service = new Guzzle4Service($client, $renderer, $this->uri, true);
        $this->assertNull($service->send($message));
    }
}
