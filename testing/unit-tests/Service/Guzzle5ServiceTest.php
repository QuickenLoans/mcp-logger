<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use Mockery;
use PHPUnit_Framework_TestCase;

class Guzzle5ServiceTest extends PHPUnit_Framework_TestCase
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

    public function testSimple()
    {
        $rendered = 'rendered message';
        $contentType = 'text/xml';

        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $client = Mockery::mock('overload:GuzzleHttp\ClientInterface');
        $request = Mockery::mock('overload:GuzzleHttp\Message\Request');

        $renderer
            ->shouldReceive('contentType')
            ->andReturn($contentType);

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn($rendered);

        $client
            ->shouldReceive('createRequest')
            ->with('POST', Mockery::any(), [
                'body' => $rendered,
                'headers' => ['Content-Type' => $contentType],
                'exceptions' => true
            ])
            ->andReturn($request);

        $client
            ->shouldReceive('send')
            ->with($request);

        $service = new Guzzle5Service($client, $renderer, $this->uri, false, false);
        $service->send($message);
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testErrorNotSilent()
    {
        $rendered = 'rendered message';
        $contentType = 'text/xml';

        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $client = Mockery::mock('overload:GuzzleHttp\ClientInterface');
        $request = Mockery::mock('overload:GuzzleHttp\Message\Request');
        $exception = Mockery::mock('GuzzleHttp\Exception\RequestException');

        $renderer
            ->shouldReceive('contentType')
            ->andReturn($contentType);

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn($rendered);

        $client
            ->shouldReceive('createRequest')
            ->with('POST', Mockery::any(), [
                'body' => $rendered,
                'headers' => ['Content-Type' => $contentType],
                'exceptions' => true
            ])
            ->andReturn($request);

        $client
            ->shouldReceive('send')
            ->with($request)
            ->andThrow($exception);

        $service = new Guzzle5Service($client, $renderer, $this->uri, false, false);
        $service->send($message);
    }

    public function testErrorSilent()
    {
        $rendered = 'rendered message';
        $contentType = 'text/xml';

        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $client = Mockery::mock('overload:GuzzleHttp\ClientInterface');
        $request = Mockery::mock('overload:GuzzleHttp\Message\Request');
        $exception = Mockery::mock('GuzzleHttp\Exception\RequestException');

        $renderer
            ->shouldReceive('contentType')
            ->andReturn($contentType);

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn($rendered);

        $client
            ->shouldReceive('createRequest')
            ->with('POST', Mockery::any(), [
                'body' => $rendered,
                'headers' => ['Content-Type' => $contentType],
                'exceptions' => true
            ])
            ->andReturn($request);

        $client
            ->shouldReceive('send')
            ->with($request)
            ->andThrow($exception);

        $service = new Guzzle5Service($client, $renderer, $this->uri, true, false);
        $service->send($message);
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testMultipleErrorsNotSilent()
    {
        $rendered = 'rendered message';
        $contentType = 'text/xml';

        $message = Mockery::mock('MCP\Logger\MessageInterface');

        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $client = Mockery::mock('overload:GuzzleHttp\ClientInterface');
        $request = Mockery::mock('overload:GuzzleHttp\Message\Request');
        $exception = Mockery::mock('GuzzleHttp\Exception\RequestException');

        $renderer
            ->shouldReceive('contentType')
            ->andReturn($contentType);

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn($rendered);

        $client
            ->shouldReceive('createRequest')
            ->with('POST', Mockery::any(), [
                'body' => $rendered,
                'headers' => ['Content-Type' => $contentType],
                'exceptions' => true
            ])
            ->andReturn($request);

        $client
            ->shouldReceive('send')
            ->with($request)
            ->andThrow($exception);

        $service = new Guzzle5Service($client, $renderer, $this->uri, false, false, 5);
        $service->send($message);
        $service->send($message);
        $service->flush();
    }

    public function testFlushNoneSent()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $client = Mockery::mock('overload:GuzzleHttp\ClientInterface');

        $service = new Guzzle5Service($client, $renderer, $this->uri, false, false, 5);
        $service->flush();
    }
}
