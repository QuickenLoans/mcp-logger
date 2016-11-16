<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger;

use MCP\Service\Logger\MessageInterface;

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
