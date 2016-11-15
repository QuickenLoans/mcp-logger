<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

/**
 * Valid log severities for the QL structured log message standard.
 */
interface QLLogLevel
{
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warn';
    const ERROR = 'error';
    const FATAL = 'fatal';
    const AUDIT = 'audit';
}
