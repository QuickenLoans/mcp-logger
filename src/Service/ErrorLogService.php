<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use QL\MCP\Logger\Exception;
use QL\MCP\Logger\ServiceInterface;

class ErrorLogService implements ServiceInterface
{
    // Configuration Keys
    const CONFIG_TYPE = 'type';
    const CONFIG_FILE = 'file';

    // Configuration Defaults
    const DEFAULT_TYPE = self::OPERATING_SYSTEM;
    const DEFAULT_FILE = '';

    // Enums
    const OPERATING_SYSTEM = 0;
    const FILE = 3;
    const SAPI = 4;

    const VALID_TYPES = [
        self::OPERATING_SYSTEM,
        self::SAPI,
        self::FILE
    ];

    const VALID_TYPES_TEXT = [
        'OPERATING_SYSTEM',
        'SAPI',
        'FILE'
    ];

    // Error Messages
    const ERR_INVALID_TYPE = 'Invalid error log type specified.';
    const ERR_INVALID_FILE = 'File destination must be provided when using FILE error log type.';

    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $type = $config[static::CONFIG_TYPE] ?? '';
        if (in_array($type, self::VALID_TYPES_TEXT) && defined("static::${type}")) {
            $config[static::CONFIG_TYPE] = constant("static::${type}");
        }

        $this->config = $config + [
            self::CONFIG_FILE => self::DEFAULT_FILE,
            self::CONFIG_TYPE => self::DEFAULT_TYPE
        ];

        $this->validateMessageType();
    }

    /**
     * @param string $level
     * @param string $formatted
     *
     * @return bool
     */
    public function send(string $level, string $formatted): bool
    {
        if ($this->config[self::CONFIG_TYPE] === self::FILE) {
            return error_log($formatted . "\n", $this->config[self::CONFIG_TYPE], $this->config[self::CONFIG_FILE]);
        } else {
            return error_log($formatted, $this->config[self::CONFIG_TYPE]);
        }
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    private function validateMessageType()
    {
        if (!in_array($this->config[self::CONFIG_TYPE], self::VALID_TYPES, true)) {
            throw new Exception(self::ERR_INVALID_TYPE);
        }

        if ($this->config[self::CONFIG_TYPE] === self::FILE && !$this->config[self::CONFIG_FILE]) {
            throw new Exception(self::ERR_INVALID_FILE);
        }
    }
}
