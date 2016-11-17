<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service\Serializer;

use QL\MCP\Logger\Message\Message;
use QL\MCP\Logger\Testing\FixtureLoadingTestCase;

class XMLSerializerTest extends FixtureLoadingTestCase
{
    /**
     * @dataProvider providerFixtureNames
     */
    public function test($fixtureName)
    {
        $messageFixture = $this->loadPhpFixture(sprintf('%s.phpd', $fixtureName));
        $message = new Message($messageFixture);

        $renderer = new XMLSerializer;
        $this->assertSame(
            $this->loadRawFixture(sprintf('%s.xml', $fixtureName)),
            $renderer($message)
        );
    }

    public function testContentType()
    {
        $renderer = new XMLSerializer;
        $this->assertEquals('text/xml', $renderer->contentType());
    }

    public function providerFixtureNames()
    {
        return [
            ['minimum-properties'],
            ['all-properties']
        ];
    }
}
