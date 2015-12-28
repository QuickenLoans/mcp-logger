<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Adapter\Psr;

use Mockery;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\Clock;
use QL\MCP\Common\Time\TimePoint;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class MessageFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testUnknownLevelDefaultsToCoreErrorLevel()
    {
        $time = Mockery::mock(TimePoint::class);
        $clock = Mockery::mock(Clock::class);
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $defaults = array(
            'applicationId' => 1,
            'machineIPAddress' => Mockery::mock(IPv4Address::class),
            'machineName' => 'Test'
        );

        $factory = new MessageFactory($clock, $defaults);

        $message = $factory->buildMessage('', 'hello');
        $this->assertSame(MessageFactory::ERROR, $message->level());
    }

    public function testCoreLevelProvidedReturnsDefaultErrorLevel()
    {
        $time = Mockery::mock(TimePoint::class);
        $clock = Mockery::mock(Clock::class);
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $defaults = array(
            'applicationId' => 1,
            'machineIPAddress' => Mockery::mock(IPv4Address::class),
            'machineName' => 'Test'
        );

        $factory = new MessageFactory($clock, $defaults);

        $message = $factory->buildMessage(MessageFactory::INFO, 'hello');
        $this->assertSame(MessageFactory::ERROR, $message->level());
    }

    public function testPsr3LogLevelIsConvertedCorrectly()
    {
        $time = Mockery::mock(TimePoint::class);
        $clock = Mockery::mock(Clock::class);
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $defaults = array(
            'applicationId' => 1,
            'machineIPAddress' => Mockery::mock(IPv4Address::class),
            'machineName' => 'Test'
        );

        $factory = new MessageFactory($clock, $defaults);

        $message = $factory->buildMessage(LogLevel::INFO, 'hello');
        $this->assertSame(MessageFactory::INFO, $message->level());
    }

}
