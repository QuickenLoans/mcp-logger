<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use Psr\Log\LogLevel;
use QL\MCP\Logger\ServiceInterface;

/**
 * Logging service for sending logs to Syslog
 */
class SyslogService implements ServiceInterface
{
    // Configuration Keys
    const CONFIG_IDENT = 'ident';
    const CONFIG_FACILITY = 'facility';
    const CONFIG_OPTIONS = 'options';

    // Configuration Defaults
    const DEFAULT_IDENT = '';
    const DEFAULT_FACILITY = LOG_USER;
    const DEFAULT_OPTIONS = LOG_ODELAY | LOG_CONS;

    /**
     * @var array
     */
    private $config;

    /**
     * @var bool
     */
    private $status;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            self::CONFIG_IDENT => self::DEFAULT_IDENT,
            self::CONFIG_FACILITY => self::DEFAULT_FACILITY,
            self::CONFIG_OPTIONS =>  self::DEFAULT_OPTIONS,
        ], $config);

        $this->status = false;
    }

    /**
     * @param string $level
     * @param string $formatted
     *
     * @return bool
     */
    public function send(string $level, string $formatted): bool
    {
        if ($this->status === false) {
            $this->connect();
            if ($this->status === false) {
                return false;
            }
        }

        $priority = $this->convertLogLevelFromPSRToSyslog($level);
        $this->status = syslog($priority, $formatted);

        return !($this->status === false);
    }

    /**
     * Attempt to connect to open syslog connection
     *
     * @return void
     */
    private function connect()
    {
        $this->status = openlog(
            $this->config[self::CONFIG_IDENT],
            $this->config[self::CONFIG_OPTIONS],
            $this->config[self::CONFIG_FACILITY]
        );
    }

    /**
     * Translate a PRS-3 log level to Syslog log level
     *
     * @param string $severity
     *
     * @return int
     */
    private function convertLogLevelFromPSRToSyslog($severity)
    {
        switch ($severity) {
            case LogLevel::DEBUG:
                return LOG_DEBUG;
            case LogLevel::INFO:
                return LOG_INFO;
            case LogLevel::NOTICE:
                return LOG_NOTICE;
            case LogLevel::WARNING:
                return LOG_WARNING;
            case LogLevel::ERROR:
                return LOG_ERR;
            case LogLevel::CRITICAL:
                return LOG_CRIT;
            case LogLevel::ALERT:
                return LOG_ALERT;
            case LogLevel::EMERGENCY:
                return LOG_EMERG;
            default:
                return LOG_ERR;
        }
    }
}
