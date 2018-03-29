<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use Mockery;
use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\MessageInterface;

class NullServiceTest extends TestCase
{
    public function testNothing()
    {
        $service = new NullService;

        $message = Mockery::mock(MessageInterface::class);

        $this->assertEquals(null, $service->send($message));
    }
}
