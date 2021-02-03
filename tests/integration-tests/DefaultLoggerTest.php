<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use PHPUnit\Framework\TestCase;

class DefaultLoggerTest extends TestCase
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

    public function test()
    {
        $logger = new Logger;

        $context = [
            'testvar' => 1234
        ];

        $logger->emergency('test message - alfa', $context);
        $logger->alert('test message - bravo', $context);
        $logger->critical('test message - charlie', $context);
        $logger->error('test message - delta', $context);
        $logger->warning('test message - echo', $context);
        $logger->notice('test message - foxtrot', $context);
        $logger->info('test message - golf', $context);
        $logger->debug('test message - hotel', $context);

        $actual = file_get_contents(__DIR__ . '/errlog');

        $this->assertContains('] emergency : test message - alfa', $actual);
        $this->assertContains('] alert : test message - bravo', $actual);
        $this->assertContains('] critical : test message - charlie', $actual);
        $this->assertContains('] error : test message - delta', $actual);
        $this->assertContains('] warning : test message - echo', $actual);
        $this->assertContains('] notice : test message - foxtrot', $actual);
        $this->assertContains('] info : test message - golf', $actual);
        $this->assertContains('] debug : test message - hotel', $actual);
    }
}
