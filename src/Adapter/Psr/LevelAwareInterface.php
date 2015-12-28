<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Adapter\Psr;

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
