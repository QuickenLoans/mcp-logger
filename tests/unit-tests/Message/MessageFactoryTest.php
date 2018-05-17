<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Message;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\Clock;
use QL\MCP\Common\Time\TimePoint;
use QL\MCP\Logger\Exception;
use QL\MCP\Logger\Testing\Stringable;
use stdClass;

class MessageFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->clock = new Clock('2019-05-10 12:15:45', 'UTC');

    }

    public function testInvalidIpAddressThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("'serverIP' must be an instance of IPv4Address");

        $factory = new MessageFactory($this->clock);
        $factory->setDefaultProperty('serverIP', new stdClass);
    }

    public function testInvalidPropertyThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid property: "userAgent". Log properties must be scalars or objects that implement __toString');

        $factory = new MessageFactory($this->clock);
        $factory->setDefaultProperty('userAgent', new stdClass);
    }

    public function testInvalidContextIsRemoved()
    {
        $defaults = [
            'applicationID' => 10,
            'serverIP' => IPv4Address::create('127.0.0.1'),
            'serverHostname' => 'Test'
        ];

        $badContext = [
            'userAgent' => new stdClass
        ];

        $factory = new MessageFactory($this->clock, $defaults);
        $actual = $factory->buildMessage('', 'message', $badContext);

        $this->assertSame(null, $actual->userAgent());
    }

    public function testBuildingAMessageWithBareMinimumPropertiesThroughSetter()
    {
        $expectedMessage = 'hello';
        $expectedDefaults = [
            'applicationID' => '1',
            'serverIP' => IPv4Address::create('127.0.0.1'),
            'serverHostname' => 'Hank'
        ];

        $factory = new MessageFactory($this->clock);
        foreach ($expectedDefaults as $property => $value) {
            $factory->setDefaultProperty($property, $value);
        }
        $actual = $factory->buildMessage('', $expectedMessage);

        // Assertions on actual message
        $this->assertSame('1', $actual->applicationID());
        $this->assertSame('127.0.0.1', $actual->serverIP());
        $this->assertSame('Hank', $actual->serverHostname());

        $this->assertSame('2019-05-10', $actual->created()->format('Y-m-d', 'UTC'));
        $this->assertSame(LogLevel::ERROR, $actual->severity());
        $this->assertSame($expectedMessage, $actual->message());
    }

    public function testBuildingAMessageWithBareMinimumPropertiesThroughConstructor()
    {
        $expectedMessage = 'there';
        $expectedDefaults = [
            'applicationID' => 'ABC2',
            'serverIP' => IPv4Address::create('127.0.0.1'),
            'serverHostname' => 'Walt'
        ];

        $factory = new MessageFactory($this->clock, $expectedDefaults);
        $actual = $factory->buildMessage('', $expectedMessage);

        // Assertions on actual message
        $this->assertSame('ABC2', $actual->applicationID());
        $this->assertSame('127.0.0.1', $actual->serverIP());
        $this->assertSame('Walt', $actual->serverHostname());

        $this->assertSame('2019-05-10', $actual->created()->format('Y-m-d', 'UTC'));
        $this->assertSame(LogLevel::ERROR, $actual->severity());
        $this->assertSame($expectedMessage, $actual->message());
    }

    public function testUnknownPropertiesAddedToExtendedProperties()
    {
        $expectedDefaults = [
            'applicationID' => 10,
            'serverIP' => IPv4Address::create('127.0.0.1'),
            'serverHostname' => 'TestMachine'
        ];
        $expectedUnknownProperty = ['unknown' => new Stringable];

        $factory = new MessageFactory($this->clock, array_merge($expectedDefaults, $expectedUnknownProperty));
        $actual = $factory->buildMessage('', 'message');

        $this->assertSame(['unknown' => ''], $actual->context());
    }
}
