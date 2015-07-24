<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Renderer;

use MCP\Logger\Testing\FixtureLoadingTestCase;
use Mockery;
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
        $message = $this->buildMock($messageFixture);

        $renderer = new XmlRenderer($writer);
        $this->assertSame(
            $this->loadRawFixture(sprintf('%s.xml', $fixtureName)),
            $renderer($message)
        );
    }

    public function providerFixtureNames()
    {
        return array(
            array('minimum-properties'),
            array('minimum-valid-properties'),
            array('all-properties')
        );
    }

    public function testContentType()
    {
        $renderer = new XmlRenderer();
        $this->assertEquals('text/xml', $renderer->contentType());
    }

    public function buildMock($fixture)
    {
        if (!is_null($fixture['createTime'])) {
            $fixture['createTime'] = Mockery::mock(
                'MCP\DataType\Time\TimePoint',
                $fixture['createTime']
            );
        }

        foreach (array('machineIPAddress', 'userIPAddress') as $ipField) {
            if (!is_null($fixture[$ipField])) {
                $fixture[$ipField] = Mockery::mock(
                    'MCP\DataType\IPv4Address',
                    $fixture[$ipField]
                );
            }
        }

        return Mockery::mock('MCP\Logger\MessageInterface', $fixture);
    }
}
