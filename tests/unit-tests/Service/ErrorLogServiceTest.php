<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use Mockery;
use PHPUnit\Framework\TestCase;
use QL\MCP\Common\GUID;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\TimePoint;
use QL\MCP\Logger\Exception;
use QL\MCP\Logger\LogLevelInterface;
use QL\MCP\Logger\Message\Message;
use QL\MCP\Logger\Service\SerializerInterface;
use QL\MCP\Logger\Service\Serializer\JSONSerializer;
use QL\MCP\Logger\Service\Serializer\LineSerializer;
use ReflectionClass;

class ErrorLogServiceTest extends TestCase
{
    public static $logSetting;

    public static function setUpBeforeClass()
    {
        self::$logSetting = ini_get('error_log');
        ini_set('error_log', __DIR__ . '/errlog');
    }

    public static function tearDownAfterClass()
    {
        ini_set('error_log', self::$logSetting);
    }

    public function tearDown()
    {
        @unlink(__DIR__ . '/errlog');
    }

    public function testDefaults()
    {
        $message = new Message([
            'message' => 'taco tuesdays',
            'created' => new TimePoint(2016, 11, 15, 12, 0, 0, 'UTC'),
            'applicationID' => '12345',
            'serverIP' => IPv4Address::create('127.0.0.1'),
            'serverHostname' => 'localhost'
        ]);

        $service = new ErrorLogService;
        $service->send($message);

        $expected = <<<LOG
[2016-11-15T12:00:00.000000Z] info : taco tuesdays (App ID: 12345, Server: localhost)
LOG;

        $this->assertContains($expected, file_get_contents(__DIR__ . '/errlog'));
    }

    public function testInvalidTypeThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid error log type specified.');

        $serializer = Mockery::mock(SerializerInterface::class);

        $message = new Message([
            'message' => 'taco tuesdays',
            'created' => new TimePoint(2016, 11, 15, 12, 0, 0, 'UTC'),
            'applicationID' => '12345',
            'serverIP' => IPv4Address::create('127.0.0.1'),
            'serverHostname' => 'localhost'
        ]);

        $service = new ErrorLogService($serializer, [
            'type' => 'burrito'
        ]);
    }

    public function testInvalidFileForFileTypeThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File destination must be provided when using FILE error log type.');

        $serializer = Mockery::mock(SerializerInterface::class);

        $message = new Message([
            'message' => 'taco tuesdays',
            'created' => new TimePoint(2016, 11, 15, 12, 0, 0, 'UTC'),
            'applicationID' => '12345',
            'serverIP' => IPv4Address::create('127.0.0.1'),
            'serverHostname' => 'localhost'
        ]);

        $service = new ErrorLogService($serializer, [
            'type' => ErrorLogService::FILE
        ]);
    }

    public function testFileTypeWritesToFile()
    {
        $serializer = new JSONSerializer;

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

        $service = new ErrorLogService($serializer, [
            'type' => ErrorLogService::FILE,
            'file' => __DIR__ . '/errlog',
        ]);

        $service->send($message);
        $service->send($message);

        $expected = <<<LOG
{"ID":"$formattedGUID","AppID":"12345","Created":"2016-11-15T12:00:00.000000Z","UserIsDisrupted":false,"Level":"info","ServerIP":"127.0.0.1","ServerHostname":"localhost","Message":"taco tuesdays"}
{"ID":"$formattedGUID","AppID":"12345","Created":"2016-11-15T12:00:00.000000Z","UserIsDisrupted":false,"Level":"info","ServerIP":"127.0.0.1","ServerHostname":"localhost","Message":"taco tuesdays"}

LOG;
        $this->assertEquals($expected, file_get_contents(__DIR__ . '/errlog'));
    }

    public function testDefaultDependencies()
    {
        $service = new ErrorLogService;

        $reflected = new ReflectionClass($service);

        $serializer = $reflected->getProperty('serializer');
        $serializer->setAccessible(true);

        $this->assertInstanceOf(LineSerializer::class, $serializer->getValue($service));
    }
}
