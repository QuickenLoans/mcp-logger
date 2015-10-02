<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Renderer;

use MCP\Logger\Message\Message;
use MCP\Logger\Testing\FixtureLoadingTestCase;
use XMLWriter;

class XmlRendererTest extends FixtureLoadingTestCase
{
    /**
     * @dataProvider providerFixtureNames
     */
    public function test($fixtureName)
    {
        $writer = new XMLWriter;

        $messageFixture = $this->loadPhpFixture(sprintf('%s.phpd', $fixtureName));
        $message = new Message($messageFixture);

        $renderer = new XmlRenderer($writer);
        $this->assertSame(
            $this->loadRawFixture(sprintf('%s.xml', $fixtureName)),
            $renderer($message)
        );
    }

    public function testContentType()
    {
        $renderer = new XmlRenderer;
        $this->assertEquals('text/xml', $renderer->contentType());
    }

    public function providerFixtureNames()
    {
        return array(
            array('minimum-properties'),
            array('all-properties')
        );
    }
}
