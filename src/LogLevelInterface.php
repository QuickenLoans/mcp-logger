<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

/**
 * @api
 */
interface LogLevelInterface
{
    const DEBUG = 'Debug';
    const INFO = 'Info';
    const WARN = 'Warn';
    const ERROR = 'Error';
    const FATAL = 'Fatal';
    const AUDIT = 'Audit';
}
