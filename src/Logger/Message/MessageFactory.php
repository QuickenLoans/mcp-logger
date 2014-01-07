<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Message;

use InvalidArgumentException;
use MCP\DataType\Time\Clock;
use MCP\DataType\IPv4Address;
use MCP\Service\Logger\LogLevelInterface;
use MCP\Service\Logger\MessageFactoryInterface;
use MCP\Service\Logger\MessageInterface;

/**
 * This factory builds a message that can be sent through the logger service.
 *
 * NOTE:
 * There are several required properties. These MUST be set in the log defaults (RECOMMENDED) or log context data.
 *
 * Required properties:
 * - applicationId
 * - machineIPAddress
 * - machineName
 *
 * All unknown properties will be automatically added to the Extended Properties.
 *
 * @internal
 */
class MessageFactory implements LogLevelInterface, MessageFactoryInterface
{
    /**
     * @var string
     */
    const ERR_UNRENDERABLE = 'Log Properties must be scalars or objects that implement __toString.';

    /**
     * @var string
     */
    const ERR_INVALID_IP = "'%s' must be an instance of IPv4Address.";

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
     * @param Clock $clock
     * @param mixed[] $defaultLogProperties
     */
    public function __construct(Clock $clock, array $defaultLogProperties = array())
    {
        $this->clock = $clock;

        $this->logProperties = array();
        foreach ($defaultLogProperties as $property => $value) {
            $this->setDefaultProperty($property, $value);
        }

        $this->knownProperties = array(
            'affectedSystem',
            'applicationId',
            'categoryId',
            'exceptionData',
            'isUserDisrupted',
            'machineIPAddress',
            'machineName',
            'referrer',
            'requestMethod',
            'url',
            'userAgentBrowser',
            'userCommonId',
            'userDisplayName',
            'userIPAddress',
            'userName',
            'userScreenName'
        );
    }

    /**
     * Set a property that will be attached to all logs.
     *
     * @param string $name
     * @param mixed $value
     * @return null
     */
    public function setDefaultProperty($name, $value)
    {
        $this->validateProperty($name, $value);

        $this->logProperties[$name] = $value;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws InvalidArgumentException
     * @return null
     */
    private function validateProperty($name, $value)
    {
        if (is_scalar($value)) {
            return;
        }

        if (in_array($name, array('machineIPAddress', 'userIPAddress'), true)) {
            if ($value instanceof IPv4Address) {
                return;
            }

            throw new InvalidArgumentException(sprintf(self::ERR_INVALID_IP, $name));
        }

        if (is_object($value) && is_callable(array($value, '__toString'))) {
            return;
        }

        throw new InvalidArgumentException(self::ERR_UNRENDERABLE);
    }

    /**
     * Sanitize and instantiate a Message
     *
     * @param mixed $level A valid core log level
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function buildMessage($level, $message, array $context = array())
    {
        if (!$level) {
            $level = static::ERROR;
        }

        $messageData = array(
            'createTime' => $this->clock->read(),
            'extendedProperties' => array(),
            'level' => $level,
            'message' => $message
        );

        $messageData = $this->consume($messageData, $this->logProperties);

        // Log context data will supercede the defaults
        $messageData = $this->consume($messageData, $context);

        // We probably shouldn't do this
        if (!isset($messageData['isUserDisrupted'])) {
            $messageData['isUserDisrupted'] = (in_array($level, array(static::ERROR, static::FATAL), true));
        }

        return new Message($messageData);
    }

    /**
     * Parse the provided context data and add it to the core message payload
     *
     * @param mixed[] $messageData
     * @param mixed[] $context
     * @return mixed[]
     */
    private function consume(array $messageData, array $context)
    {
        foreach ($context as $property => $value) {
            if (in_array($property, $this->knownProperties, true)) {
                $messageData[$property] = $value;

            } else {
                $messageData['extendedProperties'][$property] = $value;
            }
        }

        return $messageData;
    }
}
