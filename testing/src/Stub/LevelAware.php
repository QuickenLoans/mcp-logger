<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Testing\Stub;

use MCP\Logger\Adapter\Psr\LevelAwareInterface;
use MCP\Logger\Adapter\Psr\LevelAwareTrait;
use MCP\Logger\LogLevelInterface;

/**
 * @codeCoverageIgnore
 */
class LevelAware implements LevelAwareInterface, LogLevelInterface
{
    use LevelAwareTrait;
}
