<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Message;

use QL\MCP\Logger\Exception;
use QL\MCP\Logger\Testing\FixtureLoadingTestCase;
use stdClass;

/**
 * @covers QL\MCP\Logger\Message\Message
 * @covers QL\MCP\Logger\Message\MessageLoadingTrait
 */
class MessageTest extends FixtureLoadingTestCase
{
    /**
     * @dataProvider providerMissingRequiredFields
     */
    public function testMissingParameterThrowsException($missingField, $expectedError)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedError);

        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture[$missingField] = null;

        $message = new Message($fixture);
    }

    public function testInvalidClassThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("'serverIP' must be an instance of 'QL\MCP\Common\IPv4Address'");

        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['serverIP'] = 'tacos';

        $message = new Message($fixture);
    }

    public function testInvalidExtendedPropertiesThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("'context' must be an instance of 'array'");

        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['context'] = 'tacos';

        $message = new Message($fixture);
    }

    public function testIndexedContextIsSkipped()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['context'] = array('tacos');

        $message = new Message($fixture);
        $this->assertSame([], $message->context());
    }

    public function testNestedArrayInExtendedPropertiesIsJsonified()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['context'] = ['key' => ['tacos']];

        $expected = <<<'JSON'
[
    "tacos"
]
JSON;

        $message = new Message($fixture);
        $this->assertSame(['key' => $expected], $message->context());
    }

    public function testNonAssociativeArrayValuesAreSkipped()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['context'] = ['this', 'that'];

        $message = new Message($fixture);
        $this->assertSame([], $message->context());
    }

    public function testUnstringableObjectIsTypeChecked()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['context'] = ['key' => new stdClass];

        $message = new Message($fixture);
        $this->assertSame(['key' => '[object] stdClass'], $message->context());
    }

    public function testResourcePropertyIsHandled()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['context'] = ['key' => fopen('php://stdout', 'r')];

        $message = new Message($fixture);
        $this->assertSame(['key' => '[resource]'], $message->context());
    }

    public function testBooleanIsHandled()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['context'] = ['key' => false];

        $message = new Message($fixture);
        $this->assertSame(['key' => 'false'], $message->context());
    }

    public function testInvalidLevelThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("'doritos locos' is not a valid log message severity.");

        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['severity'] = 'doritos locos';

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
        return [
            ['message',         "'message' is required."],
            ['created',         "'created' must be an instance of 'QL\MCP\Common\Time\TimePoint'."],
            ['applicationID',   "'applicationID' is required."],
            ['serverIP',        "'serverIP' must be an instance of 'QL\MCP\Common\IPv4Address'."],
            ['serverHostname',  "'serverHostname' is required."]
        ];
    }
}
