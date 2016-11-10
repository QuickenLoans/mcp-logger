<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Message;

use MCP\Logger\Exception;
use MCP\Logger\MessageFactoryInterface;
use MCP\Logger\MessageInterface;
use Psr\Log\LogLevel;
use QL\MCP\Common\Time\Clock;
use QL\MCP\Common\IPv4Address;

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

    /**
     * @var string
     */
    const DEFAULT_APPLICATION_ID = '200001';
    const DEFAULT_SERVER_IP = '0.0.0.0';
    const DEFAULT_SERVER_NAME = 'unknown';

    /**
     * @var Clock
     */
    private $clock;

    /**
     * @var string[]
     */
    private $logProperties;

    /**
     * @var string[]
     */
    private $knownProperties;

    /**
     * @param Clock|null $clock
     * @param mixed[] $defaultLogProperties
     */
    public function __construct(Clock $clock = null, array $defaultLogProperties = [])
    {
        $this->clock = $clock ?: new Clock('now', 'UTC');

        $this->logProperties = [];
        $this->addDefaultDefaultProperties();

        foreach ($defaultLogProperties as $property => $value) {
            $this->setDefaultProperty($property, $value);
        }

        $this->knownProperties = [
            MessageInterface::ID,
            MessageInterface::MESSAGE,
            MessageInterface::SEVERITY,
            MessageInterface::CONTEXT,
            MessageInterface::ERROR_DETAILS,
            MessageInterface::CREATED,

            MessageInterface::APPLICATION_ID,

            MessageInterface::SERVER_ENVIRONMENT,
            MessageInterface::SERVER_IP,
            MessageInterface::SERVER_HOSTNAME,

            MessageInterface::REQUEST_METHOD,
            MessageInterface::REQUEST_URL,

            MessageInterface::USER_AGENT,
            MessageInterface::USER_IP,
            MessageInterface::USER_NAME
        ];
    }

    /**
     * Set a property that will be attached to all log messages.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function setDefaultProperty($name, $value)
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
    public function buildMessage($level, $message, array $context = [])
    {
        $data = [
            MessageInterface::CREATED => $this->clock->read(),
            MessageInterface::SEVERITY => $this->validateSeverity($level),
            MessageInterface::CONTEXT => [],
            MessageInterface::MESSAGE => (string) $message
        ];

        // Append message defaults to this payload
        $data = $this->consume($data, $this->logProperties);

        // Append message context to this payload
        $data = $this->consume($data, $context);

        return new Message($data);
    }

    /**
     * @return void
     */
    protected function addDefaultDefaultProperties()
    {
        $this->setDefaultProperty(MessageInterface::APPLICATION_ID, static::DEFAULT_APPLICATION_ID);
        $this->setDefaultProperty(MessageInterface::SERVER_IP, IPv4Address::create(static::DEFAULT_SERVER_IP));
        $this->setDefaultProperty(MessageInterface::SERVER_HOSTNAME, static::DEFAULT_SERVER_NAME);
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
            if (in_array($property, $this->knownProperties, true)) {
                $data[$property] = $value;

            } else {
                $data[MessageInterface::CONTEXT][$property] = $value;
            }
        }

        return $data;
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
