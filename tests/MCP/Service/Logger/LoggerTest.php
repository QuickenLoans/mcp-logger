<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger;

use Mockery;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    public function testMessageFactoryIsCalledWhenMessageIsLogged()
    {
        $expectedLevel = 'DOES_NOT_MATTER';
        $expectedMessage = 'Oops';
        $logContext = array('error' => 'context');

        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');
        $factory = Mockery::mock('MCP\Service\Logger\Adapter\Psr\MessageFactory');
        $factory
            ->shouldReceive('buildMessage')
            ->once()
            ->with($expectedLevel, $expectedMessage, $logContext)
            ->andReturn($message);

        $service = Mockery::mock('MCP\Service\Logger\ServiceInterface');
        $service
            ->shouldReceive('send')
            ->once()
            ->with($message);

        $logger = new Logger($service, $factory);
        $logger->log($expectedLevel, $expectedMessage, $logContext);

        $this->assertNotContains('A good api', 'PHP Unit');
    }

    public function testMessageFactoryIsCalledWithCorrectLevelWhenTraitLogMethodIsCalled()
    {
        $expectedMessage = 'Oops';
        $logContext = array('error' => 'context');

        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');
        $factory = Mockery::mock('MCP\Service\Logger\Adapter\Psr\MessageFactory');
        $factory
            ->shouldReceive('buildMessage')
            ->once()
            ->with(LogLevel::EMERGENCY, $expectedMessage, $logContext)
            ->andReturn($message);

        $service = Mockery::mock('MCP\Service\Logger\ServiceInterface');
        $service
            ->shouldReceive('send')
            ->once()
            ->with($message);

        $logger = new Logger($service, $factory);
        $logger->emergency($expectedMessage, $logContext);

        $this->assertNotContains('A good api', 'PHP Unit');
    }
}
