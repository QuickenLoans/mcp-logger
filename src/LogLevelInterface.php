<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

/**
 * Valid log severities for the QL structured log message standard.
 */
interface LogLevelInterface
{
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warn';
    const ERROR = 'error';
    const FATAL = 'fatal';
    const AUDIT = 'audit';
}
