<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Adapter\Psr;

/**
 * @api
 */
interface LevelAwareInterface
{
    /**
     * Translate a psr-3 log level to core log level
     *
     * @param string $level
     * @return string|null
     */
    public function convertPsr3LogLevel($level);

    /**
     * Translate a core log level to psr-3 log level
     *
     * @param string $level
     * @return string|null
     */
    public function convertCoreLogLevel($level);
}
