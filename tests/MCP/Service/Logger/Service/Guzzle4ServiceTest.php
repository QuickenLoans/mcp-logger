<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Service;

use GuzzleHttp\Exception\RequestException;
use Mockery;
use PHPUnit_Framework_TestCase;

class Guzzle4ServiceTest extends PHPUnit_Framework_TestCase
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
        $client = Mockery::mock('GuzzleHttp\ClientInterface', ['getBaseUrl' => '']);
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');

        $service = new Guzzle4Service($client, $renderer);
    }

    /**
     * @expectedException MCP\Service\Logger\Exception
     * @expectedExceptionMessage The service responded with an unexpected http code: '404'
     */
    public function testServiceReceivesNon200ResponseThrowsException()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

        $client = Mockery::mock('GuzzleHttp\ClientInterface', [
            'getBaseUrl' => '/some/url',
            'post' => Mockery::mock('GuzzleHttp\Message\Response', ['getStatusCode' => 404])
        ]);

        $renderer
            ->shouldReceive('__invoke')
            ->once()
            ->with($message)
            ->andReturn('rendered message');

        $service = new Guzzle4Service($client, $renderer);
        $service->send($message);
    }

    /**
     * @expectedException GuzzleHttp\Exception\RequestException
     * @expectedExceptionMessage msg
     */
    public function testExceptionIsNotCaughtWhenServiceThrowsHttpException()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

        $client = Mockery::mock('GuzzleHttp\ClientInterface', [
            'getBaseUrl' => '/some/url'
        ]);

        $client
            ->shouldReceive('post')
            ->andThrow(new RequestException('msg', Mockery::mock('GuzzleHttp\Message\RequestInterface')));

        $renderer
            ->shouldReceive('__invoke')
            ->once()
            ->with($message)
            ->andReturn('rendered message');

        $service = new Guzzle4Service($client, $renderer);
        $service->send($message);
    }

    public function testServiceReceivesNon200ResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

        $client = Mockery::mock('GuzzleHttp\ClientInterface', [
            'getBaseUrl' => '/some/url',
            'post' => Mockery::mock('GuzzleHttp\Message\Response', ['getStatusCode' => 404])
        ]);

        $renderer
            ->shouldReceive('__invoke')
            ->once();

        $service = new Guzzle4Service($client, $renderer, true);
        $this->assertNull($service->send($message));
    }

    public function testServiceThrowsHttpExceptionResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');


        $client = Mockery::mock('GuzzleHttp\ClientInterface', [
            'getBaseUrl' => '/some/url'
        ]);

        $client
            ->shouldReceive('post')
            ->andThrow(new RequestException('msg', Mockery::mock('GuzzleHttp\Message\RequestInterface')));

        $renderer
            ->shouldReceive('__invoke')
            ->once();

        $service = new Guzzle4Service($client, $renderer, true);
        $this->assertNull($service->send($message));
    }
}
