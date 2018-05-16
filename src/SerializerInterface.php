<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use QL\MCP\Logger\MessageInterface;

interface SerializerInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return string
     */
    public function __invoke(MessageInterface $message);

    /**
     * @return string
     */
    public function contentType();
}
