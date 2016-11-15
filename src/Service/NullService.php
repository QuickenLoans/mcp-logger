<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use MCP\Logger\MessageInterface;
use MCP\Logger\ServiceInterface;

/**
 * The null logging service silently consumes all messages
 */
class NullService implements ServiceInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return null
     */
    public function send(MessageInterface $message)
    {
        // ignore all log messages
    }
}
