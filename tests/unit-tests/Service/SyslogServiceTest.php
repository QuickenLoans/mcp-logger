<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\Exception;

class SyslogServiceTest extends TestCase
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
        $service = new SyslogService;
        $service->send('info', 'taco tuesdays');

        $expected = <<<LOG
taco tuesdays
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
        $service = new SyslogService;

        $actual = $service->send($severity, 'taco tuesday');
        $this->assertSame(true, $actual);

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

    public function testConnectErrorReturnsFalse()
    {
        self::$openlogError = true;

        $service = new SyslogService;

        $actual = $service->send('critical', 'herp hooooo');
        $this->assertSame(false, $actual);
    }
}

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
