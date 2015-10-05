<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Message;

use InvalidArgumentException;
use MCP\DataType\Time\Clock;
use MCP\DataType\IPv4Address;
use MCP\Logger\LogLevelInterface;
use MCP\Logger\MessageFactoryInterface;
use MCP\Logger\MessageInterface;

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
     * @type string
     */
    const ERR_UNRENDERABLE = 'Invalid property: "%s". Log Properties must be scalars or objects that implement __toString.';
    const ERR_INVALID_IP = "'%s' must be an instance of IPv4Address.";

    /**
     * @type string
     */
    const DEFAULT_APPLICATION_ID = '200001';
    const DEFAULT_MACHINE_NAME = 'unknown';
    const DEFAULT_MACHINE_IP = '0.0.0.0';

    /**
     * @type Clock
     */
    private $clock;

    /**
     * @type string[]
     */
    private $logProperties;

    /**
     * @type string[]
     */
    private $knownProperties;

    /**
     * @param Clock|nul $clock
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
            'affectedSystem',
            'applicationId',
            'categoryId',
            'environment',
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
        ];
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
        if (is_scalar($value) || is_null($value)) {
            return;
        }

        if (in_array($name, array('machineIPAddress', 'userIPAddress'), true)) {
            if ($value instanceof IPv4Address) {
                return;
            }

            throw new InvalidArgumentException(sprintf(self::ERR_INVALID_IP, $name));
        }

        if (is_object($value) && is_callable([$value, '__toString'])) {
            return;
        }

        throw new InvalidArgumentException(sprintf(self::ERR_UNRENDERABLE, $name));
    }

    /**
     * Sanitize and instantiate a Message
     *
     * @param mixed $level A valid core log level
     * @param string $message
     * @param array $context
     * @return Message
     */
    public function buildMessage($level, $message, array $context = [])
    {
        if (!$level) {
            $level = static::ERROR;
        }

        $messageData = [
            'createTime' => $this->clock->read(),
            'extendedProperties' => array(),
            'level' => $level,
            'message' => $message
        ];

        $messageData = $this->consume($messageData, $this->logProperties);

        // Log context data will supercede the defaults
        $messageData = $this->consume($messageData, $context);

        // We probably shouldn't do this
        if (!isset($messageData['isUserDisrupted'])) {
            $messageData['isUserDisrupted'] = (in_array($level, [static::ERROR, static::FATAL], true));
        }

        return new Message($messageData);
    }

    /**
     * @return null
     */
    protected function addDefaultDefaultProperties()
    {
        $this->setDefaultProperty('applicationId', static::DEFAULT_APPLICATION_ID);
        $this->setDefaultProperty('machineIPAddress', IPv4Address::create(static::DEFAULT_MACHINE_IP));
        $this->setDefaultProperty('machineName', static::DEFAULT_MACHINE_NAME);
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
