<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Transformer;

use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\Message\Message;

class QLLogSeverityTransformerTest extends TestCase
{
    /**
     * @dataProvider providerFixtureNames
     */
    public function test($message, $expectedLevel)
    {
        $transformer = new QLLogSeverityTransformer;

        $new = $transformer($message);

        $this->assertSame($expectedLevel, $new->severity());
    }

    public function providerFixtureNames()
    {
        return [
            [new Message('emergency', 'xxx'), 'fatal'],
            [new Message('alert', 'xxx'),     'fatal'],
            [new Message('critical', 'xxx'),  'fatal'],
            [new Message('error', 'xxx'),     'error'],
            [new Message('warning', 'xxx'),   'warn'],
            [new Message('notice', 'xxx'),    'info'],
            [new Message('info', 'xxx'),      'info'],
            [new Message('debug', 'xxx'),     'debug'],
        ];
    }
}
