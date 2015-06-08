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
