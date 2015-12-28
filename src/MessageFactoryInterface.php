<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

/**
 * @api
 */
interface MessageFactoryInterface
{
    /**
     * Set a property that will be attached to all logs.
     *
     * @param string $name
     * @param mixed $value
     * @return null
     */
    public function setDefaultProperty($name, $value);

    /**
     * Sanitize and instantiate a Message
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return MessageInterface
     */
    public function buildMessage($level, $message, array $context = []);
}
