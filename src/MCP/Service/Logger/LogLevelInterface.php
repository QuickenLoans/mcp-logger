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
interface LogLevelInterface
{
    const DEBUG = 'Debug';
    const INFO = 'Info';
    const WARN = 'Warn';
    const ERROR = 'Error';
    const FATAL = 'Fatal';
    const AUDIT = 'Audit';
}
