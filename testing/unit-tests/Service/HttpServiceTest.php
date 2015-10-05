<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use Mockery;
use PHPUnit_Framework_TestCase;
use MCP\Logger\MessageInterface;
use MCP\Logger\RendererInterface;

class HttpServiceTest extends PHPUnit_Framework_TestCase
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
     * @expectedException MCP\Logger\Exception
     */
    public function testMissingHostnameThrowsException()
    {
        $pool = Mockery::mock('QL\MCP\Http\Pool');
        $renderer = Mockery::mock(RendererInterface::CLASS);

        $service = new HttpService($pool, $renderer, []);
    }

    public function testSimple()
    {
        $pool = Mockery::mock('QL\MCP\Http\Pool');
        $renderer = Mockery::mock(RendererInterface::CLASS);
        $message = Mockery::mock(MessageInterface::CLASS);
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

    public function testXmlRendererIsUsedByDefault()
    {
        $pool = Mockery::mock('QL\MCP\Http\Pool');
        $message = Mockery::mock(MessageInterface::CLASS);
        $message->shouldIgnoreMissing();

        $request = Mockery::mock('Psr\Http\Message\RequestInterface');
        $response = Mockery::mock('Psr\Http\Message\ResponseInterface');

        $pool
            ->shouldReceive('createRequest')
            ->with('POST', HttpService::DEFAULT_TEMPLATE, Mockery::on(function($v) {
                return ($v['headers']['Content-Type'] === 'text/xml');
            }))
            ->andReturn($request)
            ->twice();

        $pool
            ->shouldReceive('batch')
            ->andReturn([]);

        $service = new HttpService($pool, null, [
            HttpService::CONFIG_HOSTNAME => 'replaceme',
            HttpService::CONFIG_BUFFER_LIMIT => 5
        ]);

        $service->send($message);
        $service->send($message);
        $service->flush();
    }

    /**
     * @expectedException MCP\Logger\Exception
     */
    public function testBatchErrorsNotSilent()
    {
        $pool = Mockery::mock('QL\MCP\Http\Pool');
        $renderer = Mockery::mock(RendererInterface::CLASS);
        $message = Mockery::mock(MessageInterface::CLASS);
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

    public function testBatchErrorsSilent()
    {
        $pool = Mockery::mock('QL\MCP\Http\Pool');
        $renderer = Mockery::mock(RendererInterface::CLASS);
        $message = Mockery::mock(MessageInterface::CLASS);
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
