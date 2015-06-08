<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
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
