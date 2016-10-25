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

class LoggerFilteredTest extends PHPUnit_Framework_TestCase
{
    public function testMessageNotSentWhenMinimumLevelSet()
    {
        $logger = Mockery::mock(LoggerInterface::class);

        $filter = new LoggerFiltered($logger, LogLevel::ERROR);

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

        $filter = new LoggerFiltered($logger, LogLevel::DEBUG);

        $filter->log($level, $message);
    }
}
