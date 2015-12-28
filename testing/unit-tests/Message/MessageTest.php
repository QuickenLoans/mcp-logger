<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Message;

use MCP\Logger\Testing\FixtureLoadingTestCase;
use stdClass;

/**
 * @covers MCP\Logger\Message\Message
 * @covers MCP\Logger\Message\MessageLoadingTrait
 */
class MessageTest extends FixtureLoadingTestCase
{
    /**
     * @dataProvider providerMissingRequiredFields
     */
    public function testMissingParameterThrowsException($missingField)
    {
        $this->setExpectedException('BadFunctionCallException', sprintf("'%s' is required", $missingField));
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture[$missingField] = null;

        $message = new Message($fixture);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'machineIPAddress' must be an instance of 'QL\MCP\Common\IPv4Address'
     */
    public function testInvalidClassTypeThrowsException()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['machineIPAddress'] = 'tacos';

        $message = new Message($fixture);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'extendedProperties' must be an instance of 'array'
     */
    public function testInvalidExtendedPropertiesThrowsException()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = 'tacos';

        $message = new Message($fixture);
    }

    public function testIndexedExtendedPropertiesAreSkipped()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = array('tacos');

        $message = new Message($fixture);
        $this->assertSame([], $message->extendedProperties());
    }

    public function testNestedArrayInExtendedPropertiesIsJsonified()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = ['key' => ['tacos']];

        $expected = <<<'JSON'
[
    "tacos"
]
JSON;

        $message = new Message($fixture);
        $this->assertSame(['key' => $expected], $message->extendedProperties());
    }

    public function testNonAssociativeArrayValuesAreSkipped()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = ['this', 'that'];

        $message = new Message($fixture);
        $this->assertSame([], $message->extendedProperties());
    }

    public function testUnstringableObjectIsTypeChecked()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = ['key' => new stdClass];

        $message = new Message($fixture);
        $this->assertSame(['key' => '[object] stdClass'], $message->extendedProperties());
    }

    public function testResourcePropertyIsHandled()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = ['key' => fopen('php://stdout', 'r')];

        $message = new Message($fixture);
        $this->assertSame(['key' => '[resource]'], $message->extendedProperties());
    }

    public function testBooleanIsHandled()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = ['key' => false];

        $message = new Message($fixture);
        $this->assertSame(['key' => 'false'], $message->extendedProperties());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'doritos locos' is not a valid log level.
     */
    public function testInvalidLevelThrowsException()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['level'] = 'doritos locos';

        $message = new Message($fixture);
    }

    public function testAccessorsForMinimumProperties()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');

        $message = new Message($fixture);
        foreach ($fixture as $accessor => $expected) {
            $this->assertSame($expected, $message->$accessor(), $accessor);
        }
    }

    public function testAccessorsForDefaultProperties()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixtureOut = $this->loadPhpFixture('default-properties.phpd');

        $message = new Message($fixture);
        foreach ($fixtureOut as $accessor => $expected) {
            $this->assertEquals($expected, $message->$accessor(), $accessor);
        }
    }

    public function testAccessorsForAllProperties()
    {
        $fixture = $this->loadPhpFixture('all-properties.phpd');

        $message = new Message($fixture);
        foreach ($fixture as $accessor => $expected) {
            $this->assertSame($expected, $message->$accessor(), $accessor);
        }
    }

    public function providerMissingRequiredFields()
    {
        return array(
            array('applicationId'),
            array('createTime'),
            array('machineName'),
            array('message')
        );
    }
}
