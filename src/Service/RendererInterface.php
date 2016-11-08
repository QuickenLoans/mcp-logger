<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use MCP\Logger\MessageInterface;

/**
 * @api
 */
interface RendererInterface
{
    /**
     * @param MessageInterface $message
     * @return string
     */
    public function __invoke(MessageInterface $message);

    /**
     * @return string
     */
    public function contentType();
}
