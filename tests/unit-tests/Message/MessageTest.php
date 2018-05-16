<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Message;

use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\Exception;
use stdClass;

/**
 * @covers QL\MCP\Logger\Message\Message
 * @covers QL\MCP\Logger\Message\MessageLoadingTrait
 */
class MessageTest extends TestCase
{
    public function testInvalidClassThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("'id' must be an instance of 'QL\MCP\Common\GUID'");

        $message = new Message('info', 'hello', [
            'id' => 'xxx'
        ]);
    }

    public function testInvalidExtendedPropertiesThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("'context' must be an instance of 'array'");

        $fixturePath = __DIR__ . "/.fixtures/minimum-properties.php";
        $fixture = include $fixturePath;
        $fixture['context'] = 'tacos';

        $message = new Message('info', 'hello', $fixture);
    }

    public function testIndexedContextIsSkipped()
    {
        $fixturePath = __DIR__ . "/.fixtures/minimum-properties.php";
        $fixture = include $fixturePath;
        $fixture['context'] = array('tacos');

        $message = new Message('info', 'hello', $fixture);
        $this->assertSame([], $message->context());
    }

    public function testNestedArrayInExtendedPropertiesIsJsonified()
    {
        $fixturePath = __DIR__ . "/.fixtures/minimum-properties.php";
        $fixture = include $fixturePath;
        $fixture['context'] = ['key' => ['tacos']];

        $expected = <<<'JSONTEXT'
[
    "tacos"
]
JSONTEXT;

        $message = new Message('info', 'hello', $fixture);
        $this->assertSame(['key' => $expected], $message->context());
    }

    public function testNonAssociativeArrayValuesAreSkipped()
    {
        $fixturePath = __DIR__ . "/.fixtures/minimum-properties.php";
        $fixture = include $fixturePath;
        $fixture['context'] = ['this', 'that'];

        $message = new Message('info', 'hello', $fixture);
        $this->assertSame([], $message->context());
    }

    public function testUnstringableObjectIsTypeChecked()
    {
        $fixturePath = __DIR__ . "/.fixtures/minimum-properties.php";
        $fixture = include $fixturePath;
        $fixture['context'] = ['key' => new stdClass];

        $message = new Message('info', 'hello', $fixture);
        $this->assertSame(['key' => '[object] stdClass'], $message->context());
    }

    public function testResourcePropertyIsHandled()
    {
        $fixturePath = __DIR__ . "/.fixtures/minimum-properties.php";
        $fixture = include $fixturePath;
        $fixture['context'] = ['key' => fopen('php://stdout', 'r')];

        $message = new Message('info', 'hello', $fixture);
        $this->assertSame(['key' => '[resource]'], $message->context());
    }

    public function testBooleanIsHandled()
    {
        $fixturePath = __DIR__ . "/.fixtures/minimum-properties.php";
        $fixture = include $fixturePath;
        $fixture['context'] = ['key' => false];

        $message = new Message('info', 'hello', $fixture);
        $this->assertSame(['key' => 'false'], $message->context());
    }

    public function testAccessorsForMinimumProperties()
    {
        $fixturePath = __DIR__ . "/.fixtures/minimum-properties.php";
        $fixture = include $fixturePath;

        $message = new Message('info', 'hello', $fixture);
        foreach ($fixture as $accessor => $expected) {
            $this->assertSame($expected, $message->$accessor(), $accessor);
        }
    }

    public function testAccessorsForDefaultProperties()
    {
        $fixturePath = __DIR__ . "/.fixtures/minimum-properties.php";
        $fixture = include $fixturePath;

        $fixturePath = __DIR__ . "/.fixtures/minimum-properties-out.php";
        $fixtureOut = include $fixturePath;

        $message = new Message('info', 'hello', $fixture);
        foreach ($fixtureOut as $accessor => $expected) {
            $this->assertEquals($expected, $message->$accessor(), $accessor);
        }
    }

    public function testAccessorsForAllProperties()
    {
        $fixturePath = __DIR__ . "/.fixtures/all-properties.php";
        $fixture = include $fixturePath;

        $message = new Message('info', 'hello', $fixture);
        foreach ($fixture as $accessor => $expected) {
            $this->assertSame($expected, $message->$accessor(), $accessor);
        }
    }
}
