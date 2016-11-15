<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use QL\MCP\Logger\MessageInterface;

interface ServiceInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return void
     */
    public function send(MessageInterface $message);
}
