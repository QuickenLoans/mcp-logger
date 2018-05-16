<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

interface MessageFactoryInterface
{
    /**
     * Set a property that will be attached to all logs.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setDefaultProperty(string $name, $value): void;

    /**
     * Sanitize and instantiate a Message
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function buildMessage($level, string $message, array $context = []): MessageInterface;
}
