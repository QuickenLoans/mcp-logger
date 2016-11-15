<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use QL\MCP\Logger\Exception;
use QL\MCP\Logger\MessageInterface;
use QL\MCP\Logger\ServiceInterface;
use QL\MCP\Logger\Service\Serializer\LineSerializer;

class ErrorLogService implements ServiceInterface
{
    use LineFormatterTrait;

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

    // Error Messages
    const ERR_INVALID_TYPE = 'Invalid error log type specified.';
    const ERR_INVALID_FILE = 'File destination must be provided when using FILE error log type.';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     * @param array $configuration
     */
    public function __construct(SerializerInterface $serializer = null, array $configuration = [])
    {
        $this->serializer = $serializer ?: $this->buildDefaultSerializer();

        $this->configuration = array_merge([
            self::CONFIG_FILE => self::DEFAULT_FILE,
            self::CONFIG_TYPE => self::DEFAULT_TYPE
        ], $configuration);

        $this->validateMessageType();
    }

    /**
     * @param MessageInterface $message
     *
     * @return null
     */
    public function send(MessageInterface $message)
    {
        $formatted = call_user_func($this->serializer, $message);

        if ($this->configuration[self::CONFIG_TYPE] === self::FILE) {
            error_log($formatted . "\n", $this->configuration[self::CONFIG_TYPE], $this->configuration[self::CONFIG_FILE]);
        } else {
            error_log($formatted, $this->configuration[self::CONFIG_TYPE]);
        }
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    private function validateMessageType()
    {
        if (!in_array($this->configuration[self::CONFIG_TYPE], self::VALID_TYPES, true)) {
            throw new Exception(self::ERR_INVALID_TYPE);
        }

        if ($this->configuration[self::CONFIG_TYPE] === self::FILE && !$this->configuration[self::CONFIG_FILE]) {
            throw new Exception(self::ERR_INVALID_FILE);
        }
    }


    /**
     * @return SerializerInterface
     */
    protected function buildDefaultSerializer()
    {
        return new LineSerializer;
    }
}
