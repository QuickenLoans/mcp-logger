<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Adapter\Psr;

use MCP\Logger\LogLevelInterface;
use MCP\Logger\Testing\Stub\LevelAware;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

/**
 * @covers MCP\Logger\Adapter\Psr\LevelAwareTrait
 */
class LevelAwareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerPsr3ToCoreLevels
     */
    public function testConvertingPsr3LogLevelToCoreLogLevel($psr3Level, $expectedCoreLevel)
    {
        $aware = new LevelAware;
        $this->assertSame($expectedCoreLevel, $aware->convertPsr3LogLevel($psr3Level));
    }

    /**
     * @dataProvider providerCoreToPsr3Levels
     */
    public function testConvertingCoreLogLevelToPsr3LogLevel($coreLevel, $expectedPsr3Level)
    {
        $aware = new LevelAware;
        $this->assertSame($expectedPsr3Level, $aware->convertCoreLogLevel($coreLevel));
    }

    public function providerPsr3ToCoreLevels()
    {
        return array(
            array(LogLevel::EMERGENCY, LogLevelInterface::FATAL),
            array(LogLevel::ALERT, LogLevelInterface::FATAL),
            array(LogLevel::CRITICAL, LogLevelInterface::FATAL),
            array(LogLevel::ERROR, LogLevelInterface::ERROR),
            array(LogLevel::WARNING, LogLevelInterface::WARN),
            array(LogLevel::NOTICE, LogLevelInterface::INFO),
            array(LogLevel::INFO, LogLevelInterface::INFO),
            array(LogLevel::DEBUG, LogLevelInterface::DEBUG),
            array('bad-level', null),
            array('', null),
            array(0, null),
            array(null, null)
        );
    }

    public function providerCoreToPsr3Levels()
    {
        return array(
            array(LogLevelInterface::FATAL, LogLevel::CRITICAL),
            array(LogLevelInterface::ERROR, LogLevel::ERROR),
            array(LogLevelInterface::WARN, LogLevel::WARNING),
            array(LogLevelInterface::AUDIT, LogLevel::INFO),
            array(LogLevelInterface::INFO, LogLevel::INFO),
            array(LogLevelInterface::DEBUG, LogLevel::DEBUG),
            array('bad-level', null),
            array('', null),
            array(0, null),
            array(null, null)
        );
    }
}
