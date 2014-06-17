<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Service;

use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\Response;
use Mockery;
use PHPUnit_Framework_TestCase;

class GuzzleServiceTest extends PHPUnit_Framework_TestCase
{
    public static $logSetting;

    public static function setUpBeforeClass()
    {
        self::$logSetting = ini_get('error_log');
        ini_set('error_log', 'syslog');
    }

    public static function tearDownAfterClass()
    {
        ini_set('error_log', self::$logSetting);
    }

    /**
     * @expectedException MCP\Service\Logger\Exception
     * @expectedExceptionMessage The Http Client is missing a base url
     */
    public function testMissingUrlThrowsException()
    {
        $client = Mockery::mock('Guzzle\Http\ClientInterface', array('getBaseUrl' => ''));
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');

        $service = new GuzzleService($client, $renderer);
    }

    /**
     * @expectedException MCP\Service\Logger\Exception
     * @expectedExceptionMessage The service responded with an unexpected http code: '404'
     */
    public function testServiceReceivesNon200ResponseThrowsException()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

        $response = new Response(404);
        $request = Mockery::mock('Guzzle\Http\Message\RequestInterface', ['send' => $response]);

        $client = Mockery::mock('Guzzle\Http\ClientInterface', array('getBaseUrl' => '/some/url'));
        $client
            ->shouldReceive('post')
            ->with(null, null, 'rendered message', ['Content-Type' => 'text/xml'])
            ->andReturn($request)
            ->once();

        $renderer
            ->shouldReceive('__invoke')
            ->once()
            ->with($message)
            ->andReturn('rendered message');

        $service = new GuzzleService($client, $renderer);
        $service->send($message);
    }

    /**
     * @expectedException Guzzle\Http\Exception\RequestException
     * @expectedExceptionMessage msg
     */
    public function testExceptionIsNotCaughtWhenServiceThrowsHttpException()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

        $request = Mockery::mock('Guzzle\Http\Message\RequestInterface');
        $request
            ->shouldReceive('send')
            ->andThrow(new RequestException('msg'));

        $client = Mockery::mock('Guzzle\Http\ClientInterface', array('getBaseUrl' => '/some/url'));
        $client
            ->shouldReceive('post')
            ->with(null, null, 'rendered message', ['Content-Type' => 'text/xml'])
            ->andReturn($request)
            ->once();

        $renderer
            ->shouldReceive('__invoke')
            ->once()
            ->with($message)
            ->andReturn('rendered message');

        $service = new GuzzleService($client, $renderer);
        $service->send($message);
    }

    public function testServiceReceivesNon200ResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

        $response = new Response(500);
        $request = Mockery::mock('Guzzle\Http\Message\RequestInterface', ['send' => $response]);

        $client = Mockery::mock('Guzzle\Http\ClientInterface', array('getBaseUrl' => '/some/url'));
        $client
            ->shouldReceive('post')
            ->andReturn($request)
            ->once();

        $renderer
            ->shouldReceive('__invoke')
            ->once();

        $service = new GuzzleService($client, $renderer, true);
        $this->assertNull($service->send($message));
    }

    public function testServiceThrowsHttpExceptionResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

        $request = Mockery::mock('Guzzle\Http\Message\RequestInterface');
        $request
            ->shouldReceive('send')
            ->andThrow(new RequestException('msg'));

        $client = Mockery::mock('Guzzle\Http\ClientInterface', array('getBaseUrl' => '/some/url'));
        $client
            ->shouldReceive('post')
            ->andReturn($request)
            ->once();

        $renderer
            ->shouldReceive('__invoke')
            ->once();

        $service = new GuzzleService($client, $renderer, true);
        $this->assertNull($service->send($message));
    }
}
