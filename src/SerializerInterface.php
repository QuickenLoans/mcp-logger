<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use QL\MCP\Logger\MessageInterface;

interface SerializerInterface
{
    public function __invoke(MessageInterface $message): string;
}
