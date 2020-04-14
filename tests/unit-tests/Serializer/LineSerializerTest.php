<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Serializer;

use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\Message\Message;

class LineSerializerTest extends TestCase
{
    /**
     * @dataProvider providerFixtureNames
     */
    public function test($fixtureName)
    {
        $fixturePath = __DIR__ . "/.fixtures/${fixtureName}.php";
        $input = include $fixturePath;

        $message = new Message('info', 'hello there', $input);

        $serializer = new LineSerializer();

        $actual = $serializer($message);

        $expected = trim(file_get_contents(__DIR__ . "/.fixtures/${fixtureName}.txt"));
        $this->assertSame($expected, $actual);
    }

    public function testExtraConvenienceVariables()
    {
        $fixturePath = __DIR__ . "/.fixtures/all-properties.php";
        $input = include $fixturePath;

        $message = new Message('info', 'hello there', $input);

        $serializer = new LineSerializer([
            'template' => '[{{ severity }}] {{ shortid }} - {{ date }} --- {{ time }} --- {{ datetime }}'
        ]);

        $expected = '[info] 9e43e37a - 2016-11-08 --- 16:00:00 --- 2016-11-08 16:00:00';

        $actual = $serializer($message);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerFixtureNames
     */
    public function testCustomTemplate($fixtureName)
    {
        $fixturePath = __DIR__ . "/.fixtures/${fixtureName}.php";
        $input = include $fixturePath;

        $message = new Message('info', 'hello there', $input);

        $template = <<<'TEMPLATE_TEXT'
[{{ created }}] {{ severity }} : {{ message }}
                              - Application: {{ app }} {{ env }}
                              - Request: {{ request.method }} {{ request.url }}
                              - Server: {{ server.ip }} - {{ server.host }}
                              - User: {{ user.ip }} - {{ user.agent }}

                              - Extra:
                                    A: {{ context.key }}
                                    B: {{ context.key2 }}
                                    C: {{ context.key_value_3 }}
TEMPLATE_TEXT;

        $serializer = new LineSerializer(['template' => $template]);

        $actual = $serializer($message);

        $expected = trim(file_get_contents(__DIR__ . "/.fixtures/${fixtureName}-custom.txt"), "\n");
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
