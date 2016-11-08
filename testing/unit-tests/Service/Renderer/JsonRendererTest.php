<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service\Renderer;

use MCP\Logger\Message\Message;
use MCP\Logger\Testing\FixtureLoadingTestCase;

class JsonRendererTest extends FixtureLoadingTestCase
{
    /**
     * @dataProvider providerFixtureNames
     */
    public function test($fixtureName)
    {
        $messageFixture = $this->loadPhpFixture(sprintf('%s.phpd', $fixtureName));
        $message = new Message($messageFixture);

        $fixture = $this->loadRawFixture(sprintf('%s.json', $fixtureName));

        // because I'd like to be able to read the fixtures thank you very much
        $fixture = json_encode(json_decode($fixture, true));

        $renderer = new JsonRenderer;
        $this->assertSame(
            $fixture,
            $renderer($message)
        );
    }

    public function testContentType()
    {
        $renderer = new JsonRenderer;
        $this->assertEquals('application/json', $renderer->contentType());
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
