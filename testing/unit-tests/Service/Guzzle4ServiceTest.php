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
     * @expectedExceptionMessage msg
     */
    public function testExceptionIsCaughtWhenServiceThrowsHttpException()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $request = Mockery::mock('GuzzleHttp\Message\Request');
        $client
            ->shouldReceive('createRequest')
            ->andReturn($request)
            ->once();
        $client
            ->shouldReceive('send')
            ->andThrow(new RequestException('msg', $request));

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('rendered message');

        $service = new Guzzle4Service($client, $renderer, $this->uri, false);
        $service->send($message);
    }

    public function testServiceReceivesNon200ResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $request = Mockery::mock('GuzzleHttp\Message\Request');
        $response = Mockery::mock('GuzzleHttp\Message\Response', ['getStatusCode' => '404']);
        $client
            ->shouldReceive('createRequest')
            ->andReturn($request)
            ->once();
        $client
            ->shouldReceive('send')
            ->with($request)
            ->andReturn($response)
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
        $request = Mockery::mock('GuzzleHttp\Message\Request');
        $client
            ->shouldReceive('createRequest')
            ->andReturn($request)
            ->once();
        $client
            ->shouldReceive('send')
            ->andThrow(new RequestException('msg', $request));

        $renderer
            ->shouldReceive('__invoke')
            ->once();

        $service = new Guzzle4Service($client, $renderer, $this->uri, true);
        $this->assertNull($service->send($message));
    }
}
