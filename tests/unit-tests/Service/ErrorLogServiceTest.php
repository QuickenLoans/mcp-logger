<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\Exception;

class ErrorLogServiceTest extends TestCase
{
    public static $logSetting;

    public static function setUpBeforeClass(): void
    {
        self::$logSetting = ini_get('error_log');
        ini_set('error_log', __DIR__ . '/errlog');
    }

    public static function tearDownAfterClass(): void
    {
        ini_set('error_log', self::$logSetting);
    }

    public function tearDown(): void
    {
        @unlink(__DIR__ . '/errlog');
    }

    public function testDefaults()
    {
        $service = new ErrorLogService;
        $service->send('derp', 'hello test');

        $expected = <<<LOG
 hello test
LOG;

        $this->assertStringContainsString($expected, file_get_contents(__DIR__ . '/errlog'));
    }

    public function testInvalidTypeThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid error log type specified.');

        $service = new ErrorLogService([
            'type' => 'burrito'
        ]);
    }

    public function testInvalidFileForFileTypeThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File destination must be provided when using FILE error log type.');

        $service = new ErrorLogService([
            'type' => ErrorLogService::FILE
        ]);
    }

    public function testFileTypeWritesToFile()
    {
        $service = new ErrorLogService([
            'type' => ErrorLogService::FILE,
            'file' => __DIR__ . '/errlog',
        ]);

        $service->send('info', 'alpha 1');
        $service->send('error', 'beta 2');

        $expected = <<<LOG
alpha 1
beta 2

LOG;
        $this->assertEquals($expected, file_get_contents(__DIR__ . '/errlog'));
    }
}
