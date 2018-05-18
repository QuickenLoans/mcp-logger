<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Message;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use QL\MCP\Common\Clock;
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

    public function testInvalidPropertyThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid property: "userAgent". Log properties must be scalars or objects that implement __toString');

        $factory = new MessageFactory;
        $factory->setDefaultProperty('userAgent', new stdClass);
    }

    public function testInvalidContextIsRemoved()
    {
        $defaults = [
            'applicationID' => 10,
            'serverIP' => '127.0.0.1',
            'serverHostname' => 'Test'
        ];

        $badContext = [
            'userAgent' => new stdClass
        ];

        $factory = new MessageFactory($defaults);
        $actual = $factory->buildMessage('', 'message', $badContext);

        $this->assertSame(null, $actual->userAgent());
    }

    public function testBuildingAMessageWithBareMinimumPropertiesThroughSetter()
    {
        $expectedMessage = 'hello';
        $expectedDefaults = [
            'applicationID' => '1',
            'serverIP' => '127.0.0.1',
            'serverHostname' => 'Hank',
            'created' => $this->clock->read()
        ];

        $factory = new MessageFactory;
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
            'serverIP' => '255.255.255.255',
            'serverHostname' => 'Walt',
            'created' => $this->clock->read()
        ];

        $factory = new MessageFactory($expectedDefaults);
        $actual = $factory->buildMessage('', $expectedMessage);

        // Assertions on actual message
        $this->assertSame('ABC2', $actual->applicationID());
        $this->assertSame('255.255.255.255', $actual->serverIP());
        $this->assertSame('Walt', $actual->serverHostname());

        $this->assertSame('2019-05-10', $actual->created()->format('Y-m-d', 'UTC'));
        $this->assertSame(LogLevel::ERROR, $actual->severity());
        $this->assertSame($expectedMessage, $actual->message());
    }

    public function testUnknownPropertiesAddedToExtendedProperties()
    {
        $expectedDefaults = [
            'applicationID' => 10,
            'serverIP' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            'serverHostname' => 'TestMachine'
        ];
        $expectedUnknownProperty = ['unknown' => new Stringable];

        $factory = new MessageFactory($expectedDefaults + $expectedUnknownProperty);
        $actual = $factory->buildMessage('', 'message');

        $this->assertSame(['unknown' => ''], $actual->context());
        $this->assertSame('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $actual->serverIP());
    }
}
