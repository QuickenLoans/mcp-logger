<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use Mockery;
use PHPUnit_Framework_TestCase;

class Guzzle4ServiceTest extends PHPUnit_Framework_TestCase
{
    public $uri;
    private $log;

    public function setUp()
    {
        $this->log = ini_get('error_log');
        ini_set('error_log', __DIR__ . '/errlog');
        touch(__DIR__ . '/errlog');

        $this->uri = Mockery::mock('QL\UriTemplate\UriTemplate', [
            'expand' => 'http://corelogger'
        ]);
    }

    public function tearDown()
    {
        ini_set('error_log', $this->log);
        unlink(__DIR__ . '/errlog');
    }

    /**
     * @expectedException \MCP\Logger\Exception
     * @runInSeparateProcess
     */
    public function testExceptionIsCaughtWhenServiceThrowsHttpException()
    {
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $client = Mockery::mock('overload:GuzzleHttp\ClientInterface');
        $request = Mockery::mock('overload:GuzzleHttp\Message\Request');
        $exception = Mockery::mock('GuzzleHttp\Exception\RequestException');

        $exception
            ->shouldReceive('getStatus')
            ->andReturn(0);

        $exception
            ->shouldReceive('getMessage')
            ->andReturn('msg');

        $client
            ->shouldReceive('createRequest')
            ->andReturn($request)
            ->once();

        $client
            ->shouldReceive('send')
            ->andThrow($exception);

        $renderer
            ->shouldReceive('contentType')
            ->andReturn('text/xml');

        $renderer
            ->shouldReceive('__invoke')
            ->andReturn('rendered message');

        $service = new Guzzle4Service($client, $renderer, $this->uri, false);
        $service->send($message);
    }

    /**
     * @runInSeparateProcess
     */
    public function testServiceReceivesNon200ResponseReturnsNullWhenSilent()
    {
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $client = Mockery::mock('overload:GuzzleHttp\ClientInterface');
        $request = Mockery::mock('overload:GuzzleHttp\Message\Request');
        $response = Mockery::mock('overload:GuzzleHttp\Message\Response', ['getStatusCode' => '404']);

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
            ->shouldReceive('contentType')
            ->andReturn('text/xml');

        $renderer
            ->shouldReceive('__invoke')
            ->andReturn('');

        $service = new Guzzle4Service($client, $renderer, $this->uri, true);
        $this->assertNull($service->send($message));
    }

    /**
     * @runInSeparateProcess
     */
    public function testServiceExceptionWhenSilent()
    {
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $client = Mockery::mock('overload:GuzzleHttp\ClientInterface');
        $request = Mockery::mock('overload:GuzzleHttp\Message\Request');
        $exception = Mockery::mock('GuzzleHttp\Exception\TransferException');

        $exception
            ->shouldReceive('getStatus')
            ->andReturn(0);

        $exception
            ->shouldReceive('getMessage')
            ->andReturn('msg');

        $client
            ->shouldReceive('createRequest')
            ->andReturn($request);

        $client
            ->shouldReceive('send')
            ->andThrow($exception);

        $renderer
            ->shouldReceive('contentType')
            ->andReturn('text/xml');

        $renderer
            ->shouldReceive('__invoke')
            ->andReturn('');

        $service = new Guzzle4Service($client, $renderer, $this->uri, true);
        $service->send($message);
    }
}
