<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Serializer;

use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\Message\Message;

class JSONSerializerTest extends TestCase
{
    /**
     * @dataProvider providerFixtureNames
     */
    public function test($fixtureName)
    {
        $fixturePath = __DIR__ . "/.fixtures/${fixtureName}.php";
        $input = include $fixturePath;

        $message = new Message('info', 'hello there', $input);

        $serializer = new JSONSerializer([
            'json_options' => JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ]);

        $actual = $serializer($message);

        $expected = trim(file_get_contents(__DIR__ . "/.fixtures/${fixtureName}.json"));
        $this->assertSame($expected, $actual);
    }

    public function providerFixtureNames()
    {
        return [
            ['minimum-properties'],
            ['all-properties']
        ];
    }
}
