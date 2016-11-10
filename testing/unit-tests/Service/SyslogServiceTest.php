<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use MCP\Logger\Exception;
use MCP\Logger\LogLevelInterface;
use MCP\Logger\MessageInterface;
use Mockery;
use PHPUnit_Framework_TestCase;

global $openlogError;
global $syslogError;

function openlog($ident, $options, $facility)
{
    global $openlogError;

    if (isset($openlogError) && $openlogError === true) {
        return false;
    }

    return \openlog($ident, $options, $facility);
}

function syslog($priority, $message)
{
    global $syslogError;

    if (isset($syslogError) && $syslogError === true) {
        return false;
    }

    return \syslog($priority, $message);
}

class SyslogServiceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $openlogError;
        global $syslogError;

        $openlogError = $syslogError = false;
    }

    /**
     * @dataProvider sendData
     */
    public function testSend($level)
    {
        $message = Mockery::mock(MessageInterface::class, ['level' => $level]);

        $renderer = Mockery::mock(RendererInterface::class, ['contentType' => 'application/json']);
        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('')
            ->once();

        $service = new SyslogService($renderer);
        $service->send($message);
    }

    public function sendData()
    {
        return [
            [LogLevelInterface::DEBUG],
            [LogLevelInterface::INFO],
            [LogLevelInterface::WARNING],
            [LogLevelInterface::ERROR],
            [LogLevelInterface::FATAL],
            [LogLevelInterface::AUDIT],
            ['random_crap'],
        ];
    }

    public function testSendFailThrowsException()
    {

        global $syslogError;
        $syslogError = true;

        $message = Mockery::mock(MessageInterface::class, ['level' => 'Warn'])
            ->shouldIgnoreMissing();

        $renderer = Mockery::mock(RendererInterface::class, ['contentType' => 'application/json']);
        $renderer
            ->shouldReceive('__invoke')
            ->with($message)
            ->andReturn('')
            ->once();

        $this->setExpectedException(Exception::class);

        $service = new SyslogService($renderer, [
            SyslogService::CONFIG_SILENT => false
        ]);

        $service->send($message);
    }

    public function testConnectErrorThrowsException()
    {
        global $openlogError;
        $openlogError = true;

        $message = Mockery::mock(MessageInterface::class);
        $renderer = Mockery::mock(RendererInterface::class);

        $this->setExpectedException(Exception::class);

        $service = new SyslogService($renderer, [
            SyslogService::CONFIG_SILENT => false
        ]);

        $service->send($message);
    }
}
