<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

interface TransformerInterface
{
    /**
     * Transform a message before it is serialized.
     *
     * Use this to:
     *  - Change property values
     *  - Add additional information
     *  - Remove information
     */
    public function __invoke(MessageInterface $message): MessageInterface;
}
