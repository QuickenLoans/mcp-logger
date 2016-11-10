<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service\Serializer;

use MCP\Logger\Message\Message;
use MCP\Logger\Testing\FixtureLoadingTestCase;

class JSONSerializerTest extends FixtureLoadingTestCase
{
    /**
     * @dataProvider providerFixtureNames
     */
    public function test($fixtureName)
    {
        $options = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;

        $messageFixture = $this->loadPhpFixture(sprintf('%s.phpd', $fixtureName));
        $message = new Message($messageFixture);

        $fixture = $this->loadRawFixture(sprintf('%s.json', $fixtureName));
        $fixture = trim($fixture);

        $serializer = new JSONSerializer(['json_options' => $options]);
        $this->assertSame($fixture, $serializer($message));
    }

    public function testContentType()
    {
        $serializer = new JSONSerializer;
        $this->assertEquals('application/json', $serializer->contentType());
    }

    public function providerFixtureNames()
    {
        return [
            ['minimum-properties'],
            ['all-properties'],
            ['large-properties']
        ];
    }
}
