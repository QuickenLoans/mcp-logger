<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service\Serializer;

use QL\MCP\Logger\Testing\FixtureLoadingTestCase;

class XMLGeneratorTest extends FixtureLoadingTestCase
{
    /**
     * @dataProvider providerFixtureNames
     */
    public function test($fixtureName)
    {
        $document = $this->loadPhpFixture(sprintf('%s.phpd', $fixtureName));

        $generator = new XMLGenerator;
        $this->assertSame(
            $this->loadRawFixture(sprintf('%s.xml', $fixtureName)),
            $generator->generate($document)
        );
    }

    public function providerFixtureNames()
    {
        return [
            ['simple'],
            ['complex'],
            ['with-attributes']
        ];
    }
}
