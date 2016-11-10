<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Mockery;

class FilterLoggerTest extends PHPUnit_Framework_TestCase
{
    public function testMessageNotSentWhenMinimumLevelSet()
    {
        $logger = Mockery::mock(LoggerInterface::class);

        $filter = new FilterLogger($logger, LogLevel::ERROR);

        $filter->debug('test');
    }

    public function testMessageSentWhenAboveMinimum()
    {
        $level = LogLevel::ERROR;
        $message = 'test';

        $logger = Mockery::mock(LoggerInterface::class);

        $logger
            ->shouldReceive('log')
            ->once()
            ->with($level, $message, []);

        $filter = new FilterLogger($logger, LogLevel::DEBUG);

        $filter->log($level, $message);
    }

    public function testSetGetMinimumLevel()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $filter = new FilterLogger($logger, LogLevel::DEBUG);

        $this->assertEquals(LogLevel::DEBUG, $filter->getLevel());

        $filter->setLevel(LogLevel::WARNING);

        $this->assertEquals(LogLevel::WARNING, $filter->getLevel());
    }

    public function testGetDefaultLevel()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $filter = new FilterLogger($logger);

        $this->assertEquals(LogLevel::DEBUG, $filter->getLevel());
    }

    public function testSetValidLevel()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $filter = new FilterLogger($logger);
        $filter->setLevel(LogLevel::WARNING);

        $this->assertEquals(LogLevel::WARNING, $filter->getLevel());
    }

    public function testSetInvalidLevel()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $filter = new FilterLogger($logger);
        $filter->setLevel('foo');

        $this->assertEquals(LogLevel::DEBUG, $filter->getLevel());
    }

    public function testShouldLogInvalidLevel()
    {
        $logger = Mockery::mock(LoggerInterface::class);

        $level = 'foo';
        $message = 'this is a message';

        $logger
            ->shouldReceive('log')
            ->once()
            ->with($level, $message, []);

        $filter = new FilterLogger($logger);
        $filter->setLevel(LogLevel::WARNING);

        $filter->log($level, $message);
    }

    public function testShouldLogAboveLevel()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $filter = new FilterLogger($logger);
        $filter->setLevel(LogLevel::WARNING);

        $level = LogLevel::ERROR;
        $message = 'this is a message';

        $logger
            ->shouldReceive('log')
            ->once()
            ->with($level, $message, []);

        $filter->log($level, $message);
    }

    public function testShouldLogAtLevel()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $filter = new FilterLogger($logger);
        $filter->setLevel(LogLevel::WARNING);

        $level = LogLevel::WARNING;
        $message = 'this is a message';

        $logger
            ->shouldReceive('log')
            ->once()
            ->with($level, $message, []);

        $filter->log($level, $message);
    }

    public function testShouldLogBelowLevel()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $filter = new FilterLogger($logger);
        $filter->setLevel(LogLevel::WARNING);

        $level = LogLevel::DEBUG;
        $message = 'this is a message';

        $filter->log($level, $message);
    }
}
