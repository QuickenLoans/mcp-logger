<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use PHPUnit\Framework\TestCase;

class NullServiceTest extends TestCase
{
    public function testNothing()
    {
        $service = new NullService;

        $this->assertEquals(true, $service->send('debug', 'test message'));
    }
}
