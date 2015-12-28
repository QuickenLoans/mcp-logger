<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Message;

use MCP\Logger\Testing\Mock\Stringable;
use Mockery;
use PHPUnit_Framework_TestCase;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\Clock;
use QL\MCP\Common\Time\TimePoint;
use stdClass;

class MessageFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'machineIPAddress' must be an instance of IPv4Address
     */
    public function testInvalidIpAddressThrowsException()
    {
        $clock = Mockery::mock(Clock::class);
        $factory = new MessageFactory($clock);
        $factory->setDefaultProperty('machineIPAddress', new stdClass);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Log Properties must be scalars or objects that implement __toString
     */
    public function testInvalidPropertyThrowsException()
    {
        $clock = Mockery::mock(Clock::class);
        $factory = new MessageFactory($clock);
        $factory->setDefaultProperty('userAgentBrowser', new stdClass);
    }

    public function testInvalidContextIsNotValidated()
    {
        $time = Mockery::mock(TimePoint::class);
        $clock = Mockery::mock(Clock::class);
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $defaults = array(
            'applicationId' => 10,
            'machineIPAddress' => Mockery::mock(IPv4Address::class),
            'machineName' => 'Test'
        );

        $badContext = array('userAgentBrowser' => new stdClass);

        $factory = new MessageFactory($clock, $defaults);
        $actual = $factory->buildMessage('', 'message', $badContext);

        $this->assertSame($badContext['userAgentBrowser'], $actual->userAgentBrowser());
    }

    public function testBuildingAMessageWithBareMinimumPropertiesThroughSetter()
    {
        $time = Mockery::mock(TimePoint::class);
        $clock = Mockery::mock(Clock::class);
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $expectedMessage = 'hello';
        $expectedDefaults = array(
            'applicationId' => 1,
            'machineIPAddress' => Mockery::mock(IPv4Address::class),
            'machineName' => 'Hank'
        );

        $factory = new MessageFactory($clock);
        foreach ($expectedDefaults as $property => $value) {
            $factory->setDefaultProperty($property, $value);
        }
        $actual = $factory->buildMessage('', $expectedMessage);

        // Assertions on actual message
        foreach ($expectedDefaults as $accessor => $expectedValue) {
            $this->assertSame($expectedValue, $actual->$accessor());
        }

        $this->assertSame($time, $actual->createTime());
        $this->assertSame(MessageFactory::ERROR, $actual->level());
        $this->assertTrue($actual->isUserDisrupted());
        $this->assertSame($expectedMessage, $actual->message());
    }

    public function testBuildingAMessageWithBareMinimumPropertiesThroughConstructor()
    {
        $time = Mockery::mock(TimePoint::class);
        $clock = Mockery::mock(Clock::class);
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $expectedMessage = 'there';
        $expectedDefaults = array(
            'applicationId' => 2,
            'machineIPAddress' => Mockery::mock(IPv4Address::class),
            'machineName' => 'Walt'
        );

        $factory = new MessageFactory($clock, $expectedDefaults);
        $actual = $factory->buildMessage('', $expectedMessage);

        // Assertions on actual message
        foreach ($expectedDefaults as $accessor => $expectedValue) {
            $this->assertSame($expectedValue, $actual->$accessor());
        }

        $this->assertSame($time, $actual->createTime());
        $this->assertSame(MessageFactory::ERROR, $actual->level());
        $this->assertTrue($actual->isUserDisrupted());
        $this->assertSame($expectedMessage, $actual->message());
    }

    public function testUnknownPropertiesAddedToExtendedProperties()
    {
        $time = Mockery::mock(TimePoint::class);
        $clock = Mockery::mock(Clock::class);
        $clock
            ->shouldReceive('read')
            ->once()
            ->andReturn($time);

        $expectedDefaults = array(
            'applicationId' => 10,
            'machineIPAddress' => Mockery::mock(IPv4Address::class),
            'machineName' => 'TestMachine'
        );
        $expectedUnknownProperty = ['unknown' => new Stringable];

        $factory = new MessageFactory($clock, array_merge($expectedDefaults, $expectedUnknownProperty));
        $actual = $factory->buildMessage('', 'message');

        $this->assertSame(['unknown' => ''], $actual->extendedProperties());
    }
}
