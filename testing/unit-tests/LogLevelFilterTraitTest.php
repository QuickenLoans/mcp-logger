<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use PHPUnit_Framework_TestCase;

class LogLevelFilter
{
    use LogLevelFilterTrait {
        setMinimumLevel as public;
        getMinimumLevel as public;
        shouldLog as public;
    }
}

class LogLevelFilterTraitTest extends PHPUnit_Framework_TestCase
{
    public function testGetDefaultLevel()
    {
        $filter = new LogLevelFilter();

        $this->assertEquals(LogLevelInterface::DEBUG, $filter->getMinimumLevel());
    }

    public function testSetValidLevel()
    {
        $filter = new LogLevelFilter();
        $filter->setMinimumLevel(LogLevelInterface::WARN);

        $this->assertEquals(LogLevelInterface::WARN, $filter->getMinimumLevel());
    }

    public function testSetInvalidLevel()
    {
        $filter = new LogLevelFilter();
        $filter->setMinimumLevel('foo');

        $this->assertEquals(LogLevelInterface::DEBUG, $filter->getMinimumLevel());
    }

    public function testShouldLogInvalidLevel()
    {
        $filter = new LogLevelFilter();
        $filter->setMinimumLevel(LogLevelInterface::WARN);

        $this->assertTrue($filter->shouldLog('foo'));
    }

    public function testShouldLogAboveLevel()
    {
        $filter = new LogLevelFilter();
        $filter->setMinimumLevel(LogLevelInterface::WARN);

        $this->assertTrue($filter->shouldLog(LogLevelInterface::ERROR));
    }

    public function testShouldLogAtLevel()
    {
        $filter = new LogLevelFilter();
        $filter->setMinimumLevel(LogLevelInterface::WARN);

        $this->assertTrue($filter->shouldLog(LogLevelInterface::WARN));
    }

    public function testShouldLogBelowLevel()
    {
        $filter = new LogLevelFilter();
        $filter->setMinimumLevel(LogLevelInterface::WARN);

        $this->assertFalse($filter->shouldLog(LogLevelInterface::DEBUG));
    }
}
