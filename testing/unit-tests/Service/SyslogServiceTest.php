<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use Mockery;
use Psr\Log\LogLevel;
use PHPUnit_Framework_TestCase;
use QL\MCP\Common\GUID;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\TimePoint;
use QL\MCP\Logger\Exception;
use QL\MCP\Logger\MessageInterface;
use QL\MCP\Logger\Message\Message;
use QL\MCP\Logger\Service\Serializer\JSONSerializer;
use QL\MCP\Logger\Service\Serializer\LineSerializer;
use ReflectionClass;

function openlog()
{
    if (SyslogServiceTest::$openlogError === true) return false;

    SyslogServiceTest::$logs[] = ['openlog', func_get_args()];
    return true;
}

function syslog()
{
    if (SyslogServiceTest::$syslogError === true) return false;

    SyslogServiceTest::$logs[] = ['syslog', func_get_args()];
    return true;
}

class SyslogServiceTest extends PHPUnit_Framework_TestCase
{
    public static $openlogError;
    public static $syslogError;
    public static $logs;

    public function setUp()
    {
        self::$openlogError = self::$syslogError = false;
        self::$logs = [];
    }

    public function testDefaultSettings()
    {
        $guid = GUID::create();
        $formattedGUID = strtolower(substr($guid->asHumanReadable(), 1, -1));
        $message = new Message([
            'id' => $guid,
            'message' => 'taco tuesdays',
            'created' => new TimePoint(2016, 11, 15, 12, 0, 0, 'UTC'),
            'applicationID' => '12345',
            'serverIP' => IPv4Address::create('127.0.0.1'),
            'serverHostname' => 'localhost'
        ]);

        $service = new SyslogService;
        $service->send($message);

        $expected = <<<LOG
{"ID":"$formattedGUID","AppID":"12345","Created":"2016-11-15T12:00:00.000000Z","UserIsDisrupted":false,"Level":"info","ServerIP":"127.0.0.1","ServerHostname":"localhost","Message":"taco tuesdays"}
LOG;

        $this->assertSame('openlog', self::$logs[0][0]);
        $this->assertSame(['', 6, 8], self::$logs[0][1]);

        $this->assertSame('syslog', self::$logs[1][0]);
        $this->assertSame([6, $expected], self::$logs[1][1]);
    }

    /**
     * @dataProvider messageSeverities
     */
    public function testSendWithVariousSeverities($severity, $expectedSyslogPriority)
    {
        $guid = GUID::create();

        $message = new Message([
            'message' => 'taco tuesdays',
            'severity' => $severity,
            'created' => new TimePoint(2016, 11, 15, 12, 0, 0, 'UTC'),
            'applicationID' => '12345',
            'serverIP' => IPv4Address::create('127.0.0.1'),
            'serverHostname' => 'localhost'
        ]);

        $service = new SyslogService;
        $service->send($message);

        $syslog = self::$logs[1][1];

        $this->assertSame($expectedSyslogPriority, $syslog[0]);
    }

    public function messageSeverities()
    {
        return [
            [LogLevel::DEBUG, LOG_DEBUG],
            [LogLevel::WARNING, LOG_WARNING],
            [LogLevel::ERROR, LOG_ERR],
            [LogLevel::CRITICAL, LOG_CRIT],
            [LogLevel::EMERGENCY, LOG_EMERG]
        ];
    }

    public function testSendFailThrowsException()
    {
        self::$syslogError = true;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to send message to syslog connection. derp doooooo');

        $message = Mockery::mock(MessageInterface::class, [
            'severity' => LogLevel::CRITICAL,
            'message' => 'derp doooooo'
        ]);

        $serializer = Mockery::mock(SerializerInterface::class, ['__invoke' => null]);

        $service = new SyslogService($serializer, [
            SyslogService::CONFIG_SILENT => false
        ]);

        $service->send($message);
    }

    public function testConnectErrorThrowsException()
    {
        self::$openlogError = true;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to open syslog connection.');

        $message = Mockery::mock(MessageInterface::class, [
            'severity' => LogLevel::CRITICAL,
            'message' => 'herp hoooooo'
        ]);

        $serializer = Mockery::mock(SerializerInterface::class, ['__invoke' => null]);


        $service = new SyslogService($serializer, [
            SyslogService::CONFIG_SILENT => false
        ]);

        $service->send($message);
    }

    public function testDefaultDependencies()
    {
        $service = new SyslogService;

        $reflected = new ReflectionClass($service);

        $serializer = $reflected->getProperty('serializer');
        $serializer->setAccessible(true);

        $this->assertInstanceOf(JSONSerializer::class, $serializer->getValue($service));
    }
}
