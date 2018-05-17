<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Message;

use Psr\Log\LogLevel;
use QL\MCP\Common\GUID;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\Clock;
use QL\MCP\Common\Time\TimePoint;
use QL\MCP\Logger\Exception;
use QL\MCP\Logger\MessageFactoryInterface;
use QL\MCP\Logger\MessageInterface;

/**
 * This factory builds a message that can be sent through the logger service.
 *
 * NOTE:
 * QL structured log messages have several required properties. These MUST be set
 * in the log message defaults (RECOMMENDED) or message context.
 *
 * This factory will set default values for the following properties to ensure
 * any message constructed through this factory is valid.
 *
 * Required properties:
 * - message
 * - severity
 * - context
 *
 * - created
 * - applicationID
 * - serverIP
 * - serverHostname
 *
 * All unknown properties will be automatically added to context.
 */
class MessageFactory implements MessageFactoryInterface
{
    /**
     * @var string
     */
    const ERR_UNRENDERABLE = 'Invalid property: "%s". Log properties must be scalars or objects that implement __toString.';
    const ERR_INVALID_IP = "'%s' must be an instance of IPv4Address.";
    const ERR_INVALID_TIME = "'%s' must be an instance of TimePoint.";

    // Config Keys
    const CONFIG_MAX_PROPERTY_SIZE = 'max_size_kb';

    /**
     * @var Clock
     */
    private $clock;

    /**
     * @var string[]
     */
    private $logProperties;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var string[]
     */
    private $knownProperties;

    /**
     * @param Clock|null $clock
     * @param mixed[] $defaults
     */
    public function __construct(Clock $clock = null, array $defaults = [])
    {
        $this->clock = $clock ?: new Clock('now', 'UTC');

        $properties = $defaults + [
            MessageInterface::APPLICATION_ID => 'APP123',
            MessageInterface::SERVER_IP => IPv4Address::create('0.0.0.0'),
            MessageInterface::SERVER_HOSTNAME => gethostname(),
        ];

        $this->logProperties = [];
        foreach ($properties as $property => $value) {
            $this->setDefaultProperty($property, $value);
        }

        $this->configuration = [
            self::CONFIG_MAX_PROPERTY_SIZE => 100 // default to max 100kb text size of each property
        ];

        $this->knownProperties = [
            MessageInterface::ID,
            MessageInterface::MESSAGE,
            MessageInterface::SEVERITY,
            MessageInterface::CREATED,

            MessageInterface::CONTEXT,
            MessageInterface::DETAILS,

            MessageInterface::APPLICATION_ID,

            MessageInterface::SERVER_ENVIRONMENT,
            MessageInterface::SERVER_IP,
            MessageInterface::SERVER_HOSTNAME,

            MessageInterface::REQUEST_METHOD,
            MessageInterface::REQUEST_URL,

            MessageInterface::USER_AGENT,
            MessageInterface::USER_IP
        ];
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function configure($name, $value)
    {
        $this->configuration[$name] = $value;
    }

    /**
     * Set a property that will be attached to all log messages.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function setDefaultProperty(string $name, $value): void
    {
        $this->validateProperty($name, $value);

        $this->logProperties[$name] = $value;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @throws Exception
     *
     * @return void
     */
    private function validateProperty($name, $value)
    {
        if (in_array($name, [MessageInterface::SERVER_IP, MessageInterface::USER_IP], true)) {
            if (is_null($value) || $value instanceof IPv4Address) {
                return;
            }

            throw new Exception(sprintf(self::ERR_INVALID_IP, $name));
        }

        if (in_array($name, [MessageInterface::CREATED], true)) {
            if (is_null($value) || $value instanceof TimePoint) {
                return;
            }

            throw new Exception(sprintf(self::ERR_INVALID_TIME, $name));
        }

        if (is_scalar($value) || is_null($value)) {
            return;
        }

        if (is_object($value) && is_callable([$value, '__toString'])) {
            return;
        }

        throw new Exception(sprintf(self::ERR_UNRENDERABLE, $name));
    }

    /**
     * Sanitize and instantiate a Message
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return Message
     */
    public function buildMessage($level, string $message, array $context = []): MessageInterface
    {
        $level = $this->validateSeverity($level);
        $data = [
            MessageInterface::CONTEXT => []
        ];

        // Append message defaults to this payload
        $data = $this->consume($data, $this->logProperties);

        // Append message context to this payload
        $data = $this->consume($data, $context);

        return new Message($level, $message, $data);
    }

    /**
     * Parse the provided context data and add it to the message payload
     *
     * @param mixed[] $data
     * @param mixed[] $context
     *
     * @return mixed[]
     */
    private function consume(array $data, array $context)
    {
        foreach ($context as $property => $value) {
            $sanitized = $this->validateValue($value);
            if ($sanitized === null) {
                continue;
            }

            if (in_array($property, $this->knownProperties, true)) {
                $data[$property] = $sanitized;

            } else {
                $data[MessageInterface::CONTEXT][$property] = $sanitized;
            }
        }

        return $data;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function validateValue($value)
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof GUID || $value instanceof TimePoint) {
            return $value;
        }

        if (is_string($value)) {
            return $this->restrictValueLength($value);
        }

        if (is_object($value) && is_callable([$value, '__toString'])) {
            return $this->restrictValueLength($value);
        }

        if (is_scalar($value)) {
            return $value;
        }

        return null;
    }

    /**
     * @param string|object $value
     *
     * @return string
     */
    private function restrictValueLength($value)
    {
        $value = (string) $value;

        $max = $this->configuration[self::CONFIG_MAX_PROPERTY_SIZE];
        if (!$max) {
            return $value;
        }

        $max = $max * 1000;

        return substr($value, 0, $max);
    }

    /**
     * @param string $level
     *
     * @return string
     */
    private function validateSeverity($level)
    {
        $validLevels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG
        ];

        return in_array($level, $validLevels, true) ? $level : LogLevel::ERROR;
    }
}
