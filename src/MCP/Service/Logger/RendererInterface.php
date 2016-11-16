<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger;

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
}
