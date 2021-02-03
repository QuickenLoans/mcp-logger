<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\Message\Message;

class MemoryLoggerTest extends TestCase
{
    public function test()
    {
        $expected1 = <<<TEXT
debug : test message - alfa
TEXT;

        $expected2 = <<<TEXT
emergency : test message - bravo
TEXT;

        $logger = new MemoryLogger;

        $logger->debug('test message - alfa');
        $logger->emergency('test message - bravo');

        $messages = $logger->getMessages();

        $this->assertStringContainsString($expected1, $messages[0]);
        $this->assertStringContainsString($expected2, $messages[1]);
    }
}
