<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Adapter\Psr;

use Mockery;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class MessageFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testUnknownLevelDefaultsToCoreErrorLevel()
    {
        $time = Mockery::mock('MCP\DataType\Time\TimePoint');
        $clock = Mockery::mock('MCP\DataType\Time\Clock');
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $defaults = array(
            'applicationId' => 1,
            'machineIPAddress' => Mockery::mock('MCP\DataType\IPv4Address'),
            'machineName' => 'Test'
        );

        $factory = new MessageFactory($clock, $defaults);

        $message = $factory->buildMessage('', 'hello');
        $this->assertSame(MessageFactory::ERROR, $message->level());
    }

    public function testCoreLevelProvidedReturnsDefaultErrorLevel()
    {
        $time = Mockery::mock('MCP\DataType\Time\TimePoint');
        $clock = Mockery::mock('MCP\DataType\Time\Clock');
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $defaults = array(
            'applicationId' => 1,
            'machineIPAddress' => Mockery::mock('MCP\DataType\IPv4Address'),
            'machineName' => 'Test'
        );

        $factory = new MessageFactory($clock, $defaults);

        $message = $factory->buildMessage(MessageFactory::INFO, 'hello');
        $this->assertSame(MessageFactory::ERROR, $message->level());
    }

    public function testPsr3LogLevelIsConvertedCorrectly()
    {
        $time = Mockery::mock('MCP\DataType\Time\TimePoint');
        $clock = Mockery::mock('MCP\DataType\Time\Clock');
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $defaults = array(
            'applicationId' => 1,
            'machineIPAddress' => Mockery::mock('MCP\DataType\IPv4Address'),
            'machineName' => 'Test'
        );

        $factory = new MessageFactory($clock, $defaults);

        $message = $factory->buildMessage(LogLevel::INFO, 'hello');
        $this->assertSame(MessageFactory::INFO, $message->level());
    }

}
