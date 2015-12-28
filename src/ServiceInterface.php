<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use MCP\Logger\MessageInterface;

/**
 * @api
 */
interface ServiceInterface
{
    /**
     * @param MessageInterface $message
     * @return null
     */
    public function send(MessageInterface $message);
}
