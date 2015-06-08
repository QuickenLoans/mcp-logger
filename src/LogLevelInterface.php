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
interface LogLevelInterface
{
    const DEBUG = 'Debug';
    const INFO = 'Info';
    const WARN = 'Warn';
    const ERROR = 'Error';
    const FATAL = 'Fatal';
    const AUDIT = 'Audit';
}
