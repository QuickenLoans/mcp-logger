<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Message;

use MCP\Logger\Exception;
use MCP\Logger\Testing\Mock\Stringable;
use Mockery;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\Clock;
use QL\MCP\Common\Time\TimePoint;
use stdClass;

class MessageFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidIpAddressThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("'serverIP' must be an instance of IPv4Address");

        $clock = Mockery::mock(Clock::class);
        $factory = new MessageFactory($clock);
        $factory->setDefaultProperty('serverIP', new stdClass);
    }

    public function testInvalidPropertyThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid property: "userAgent". Log properties must be scalars or objects that implement __toString');

        $clock = Mockery::mock(Clock::class);
        $factory = new MessageFactory($clock);
        $factory->setDefaultProperty('userAgent', new stdClass);
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
            'applicationID' => 10,
            'serverIP' => Mockery::mock(IPv4Address::class),
            'serverHostname' => 'Test'
        );

        $badContext = array('userAgent' => new stdClass);

        $factory = new MessageFactory($clock, $defaults);
        $actual = $factory->buildMessage('', 'message', $badContext);

        $this->assertSame($badContext['userAgent'], $actual->userAgent());
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
            'applicationID' => 1,
            'serverIP' => Mockery::mock(IPv4Address::class),
            'serverHostname' => 'Hank'
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

        $this->assertSame($time, $actual->created());
        $this->assertSame(LogLevel::ERROR, $actual->severity());
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
            'applicationID' => 2,
            'serverIP' => Mockery::mock(IPv4Address::class),
            'serverHostname' => 'Walt'
        );

        $factory = new MessageFactory($clock, $expectedDefaults);
        $actual = $factory->buildMessage('', $expectedMessage);

        // Assertions on actual message
        foreach ($expectedDefaults as $accessor => $expectedValue) {
            $this->assertSame($expectedValue, $actual->$accessor());
        }

        $this->assertSame($time, $actual->created());
        $this->assertSame(LogLevel::ERROR, $actual->severity());
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
            'applicationID' => 10,
            'serverIP' => Mockery::mock(IPv4Address::class),
            'serverHostname' => 'TestMachine'
        );
        $expectedUnknownProperty = ['unknown' => new Stringable];

        $factory = new MessageFactory($clock, array_merge($expectedDefaults, $expectedUnknownProperty));
        $actual = $factory->buildMessage('', 'message');

        $this->assertSame(['unknown' => ''], $actual->context());
    }
}
