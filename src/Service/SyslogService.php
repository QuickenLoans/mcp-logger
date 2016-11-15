<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use MCP\Logger\Exception;
use MCP\Logger\MessageInterface;
use MCP\Logger\Service\Serializer\JSONSerializer;
use MCP\Logger\Service\Serializer\LogLevelTrait;
use MCP\Logger\ServiceInterface;

/**
 * Logging service for sending logs to Syslog
 */
class SyslogService implements ServiceInterface
{
    use LogLevelTrait;

    // Configuration Keys
    const CONFIG_SILENT = 'silent';
    const CONFIG_IDENT = 'ident';
    const CONFIG_FACILITY = 'facility';
    const CONFIG_OPTIONS = 'options';

    // Configuration Defaults
    const DEFAULT_SILENT = true;
    const DEFAULT_IDENT = '';
    const DEFAULT_FACILITY = LOG_USER;
    const DEFAULT_OPTIONS = LOG_ODELAY | LOG_CONS;

    // Error Messages
    const ERR_OPEN = 'Unable to open syslog connection.';
    const ERR_SEND = 'Unable to send message to syslog connection. %s';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var bool
     */
    private $status;

    /**
     * @param SerializerInterface|null $serializer
     * @param array $configuration
     */
    public function __construct(SerializerInterface $serializer = null, array $configuration = [])
    {
        $this->configuration = array_merge([
            self::CONFIG_SILENT => self::DEFAULT_SILENT,
            self::CONFIG_IDENT => self::DEFAULT_IDENT,
            self::CONFIG_FACILITY => self::DEFAULT_FACILITY,
            self::CONFIG_OPTIONS =>  self::DEFAULT_OPTIONS
        ], $configuration);

        $this->serializer = $serializer ?: $this->buildDefaultSerializer();
        $this->status = false;
    }

    /**
     * @param MessageInterface $message
     *
     * @return void
     */
    public function send(MessageInterface $message)
    {
        if ($this->status === false) {
            $this->connect();
        }

        $data = call_user_func($this->serializer, $message);
        $priority = $this->convertLogLevelFromPSRToSyslog($message->severity());
        $this->status = syslog($priority, $data);

        if ($this->status === false) {
            $this->error(sprintf(self::ERR_SEND, $message->message()));
        }
    }

    /**
     * Attempt to connect to open syslog connection
     *
     * @return void
     */
    private function connect()
    {
        $this->status = openlog(
            $this->configuration[self::CONFIG_IDENT],
            $this->configuration[self::CONFIG_OPTIONS],
            $this->configuration[self::CONFIG_FACILITY]
        );

        if ($this->status === false) {
            return $this->error(self::ERR_OPEN);
        }
    }

    /**
     * Handle an error
     *
     * @param string $message
     *
     * @throws Exception
     *
     * @return bool
     */
    private function error($message)
    {
        if ($this->configuration[self::CONFIG_SILENT]) {
            return error_log($message);
        }

        throw new Exception($message);
    }

    /**
     * @return SerializerInterface
     */
    protected function buildDefaultSerializer()
    {
        return new JSONSerializer;
    }
}
