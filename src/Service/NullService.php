<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use QL\MCP\Logger\ServiceInterface;

/**
 * The null logging service silently consumes all messages
 */
class NullService implements ServiceInterface
{
    /**
     * @param string $level
     * @param string $formatted
     *
     * @return bool
     */
    public function send(string $level, string $formatted): bool
    {
        return true;
    }
}
