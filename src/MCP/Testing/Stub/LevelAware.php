<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Testing\Stub;

use MCP\Service\Logger\Adapter\Psr\LevelAwareInterface;
use MCP\Service\Logger\Adapter\Psr\LevelAwareTrait;
use MCP\Service\Logger\LogLevelInterface;

/**
 * @codeCoverageIgnore
 */
class LevelAware implements LevelAwareInterface, LogLevelInterface
{
    use LevelAwareTrait;
}
