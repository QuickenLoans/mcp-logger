<?php

namespace MCP\Logger\Service;

use Mockery;

class HttpServiceTest extends \PHPUnit_Framework_TestCase
{
    private $log;

    public function setUp()
    {
        $this->log = ini_get('error_log');
        ini_set('error_log', __DIR__ . '/errlog');
        touch(__DIR__ . '/errlog');
    }

    public function tearDown()
    {
        ini_set('error_log', $this->log);
        unlink(__DIR__ . '/errlog');
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testMissingHostname()
    {
        $pool = Mockery::mock('QL\MCP\Http\Pool');
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');

        $service = new HttpService($pool, $renderer, []);
    }

    public function testSimple()
    {
        $pool = Mockery::mock('QL\MCP\Http\Pool');
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');
        $request = Mockery::mock('Psr\Http\Message\RequestInterface');
        $response = Mockery::mock('Psr\Http\Message\ResponseInterface');

        $pool
            ->shouldReceive('createRequest')
            ->with('POST', HttpService::DEFAULT_TEMPLATE, Mockery::any())
            ->andReturn($request);

        $pool
            ->shouldReceive('batch')
            ->with([$request, $request], Mockery::any())
            ->andReturn([$response, $response]);

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('abc123');

        $renderer
            ->shouldReceive('contentType')
            ->andReturn('text/xml');

        $service = new HttpService($pool, $renderer, [
            HttpService::CONFIG_HOSTNAME => 'replaceme',
            HttpService::CONFIG_BUFFER_LIMIT => 5
        ]);

        $service->send($message);
        $service->send($message);
        $service->flush();
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testBatchErrorsNotSilent()
    {
        $pool = Mockery::mock('QL\MCP\Http\Pool');
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');
        $request = Mockery::mock('Psr\Http\Message\RequestInterface');
        $exception = new \Exception('error');

        $pool
            ->shouldReceive('createRequest')
            ->with('POST', HttpService::DEFAULT_TEMPLATE, Mockery::any())
            ->andReturn($request);

        $pool
            ->shouldReceive('batch')
            ->with([$request], Mockery::any())
            ->andReturn([$exception]);

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('abc123');

        $renderer
            ->shouldReceive('contentType')
            ->andReturn('text/xml');

        $service = new HttpService($pool, $renderer, [
            HttpService::CONFIG_HOSTNAME => 'replaceme',
            HttpService::CONFIG_BUFFER_LIMIT => 5,
            HttpService::CONFIG_SILENT => false
        ]);

        $service->send($message);
        $service->flush();
    }

    public function testBatchErrorsSilen()
    {
        $pool = Mockery::mock('QL\MCP\Http\Pool');
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');
        $request = Mockery::mock('Psr\Http\Message\RequestInterface');
        $exception = new \Exception('error');

        $pool
            ->shouldReceive('createRequest')
            ->with('POST', HttpService::DEFAULT_TEMPLATE, Mockery::any())
            ->andReturn($request);

        $pool
            ->shouldReceive('batch')
            ->with([$request], Mockery::any())
            ->andReturn([$exception]);

        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('abc123');

        $renderer
            ->shouldReceive('contentType')
            ->andReturn('text/xml');

        $service = new HttpService($pool, $renderer, [
            HttpService::CONFIG_HOSTNAME => 'replaceme',
            HttpService::CONFIG_BUFFER_LIMIT => 5,
            HttpService::CONFIG_SILENT => true
        ]);

        $service->send($message);
        $service->flush();

        $this->assertContains('error', file_get_contents(__DIR__ . '/errlog'));
    }
}