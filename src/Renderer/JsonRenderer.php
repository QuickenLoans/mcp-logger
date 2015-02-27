<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Renderer;

use DateTime;
use MCP\Logger\MessageInterface;
use MCP\Logger\RendererInterface;

/**
 * @internal
 */
class JsonRenderer implements RendererInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return string
     */
    public function __invoke(MessageInterface $message)
    {
        $data = [];

        // Required
        $this->addProperty($data, 'ApplicationId', $this->sanitizeInteger($message->applicationId()));

        $date = ($message->createTime() !== null) ? $message->createTime()->format(DateTime::RFC3339, 'UTC') : null;
        $this->addProperty($data, 'CreateTime', $date);

        $this->addProperty($data, 'Level', $this->sanitizeString($message->level()));
        $this->addProperty($data, 'Message', $this->sanitizeString($message->message()));

        $this->addProperty($data, 'IsUserDisrupted', $this->sanitizeBoolean($message->isUserDisrupted()));
        $this->addExtendedProperties($data, $message->extendedProperties());

        $ip = ($message->machineIPAddress() !== null) ? $message->machineIPAddress()->asString() : null;
        $this->addProperty($data, 'MachineIPAddress', $ip);
        $this->addProperty($data, 'MachineName', $this->sanitizeString($message->machineName()));

        // Optional
        $this->addOptionalProperty($data, 'ExceptionData', $this->sanitizeString($message->exceptionData()));

        $this->addOptionalProperty($data, 'AffectedSystem', $this->sanitizeString($message->affectedSystem()));
        $this->addOptionalProperty($data, 'CategoryId', $this->sanitizeInteger($message->categoryId()));
        $this->addOptionalProperty($data, 'Referrer', $this->sanitizeString($message->referrer()));
        $this->addOptionalProperty($data, 'RequestMethod', $this->sanitizeString($message->requestMethod()));
        $this->addOptionalProperty($data, 'Url', $this->sanitizeString($message->url()));
        $this->addOptionalProperty($data, 'UserAgentBrowser', $this->sanitizeString($message->userAgentBrowser()));
        $this->addOptionalProperty($data, 'UserCommonId', $this->sanitizeInteger($message->userCommonId()));
        $this->addOptionalProperty($data, 'UserDisplayName', $this->sanitizeString($message->userDisplayName()));

        $ip = ($message->userIPAddress() !== null) ? $message->userIPAddress()->asString() : null;
        $this->addOptionalProperty($data, 'UserIPAddress', $ip);

        $this->addOptionalProperty($data, 'UserName', $this->sanitizeString($message->userName()));
        $this->addOptionalProperty($data, 'UserScreenName', $this->sanitizeString($message->userScreenName()));

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'application/json';
    }

    /**
     * @param array $data
     * @param mixed[] $properties
     *
     * @return null
     */
    protected function addExtendedProperties(array &$data, $properties)
    {
        if (!is_array($properties)) {
            $properties = [];
        }

        $extended = [];
        foreach ($properties as $k => $prop) {
            $extended[$k] = $this->sanitizeString($prop);
        }

        $this->addProperty($data, 'ExtendedProperties', $extended);
    }

    /**
     * @param array $data
     * @param string $name
     * @param mixed $value
     *
     * @return null
     */
    protected function addOptionalProperty(array &$data, $name, $value)
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->addProperty($data, $name, $value);
    }

    /**
     * @param array $data
     * @param string $name
     * @param mixed $value
     *
     * @return null
     */
    protected function addProperty(array &$data, $name, $value)
    {
        $data[$name] = $value;
    }

    /**
     * @param boolean $value
     *
     * @return boolean
     */
    protected function sanitizeBoolean($value)
    {
        return (bool) $value;
    }

    /**
     * @param int|string $value
     *
     * @return int
     */
    protected function sanitizeInteger($value)
    {
        if ($res = filter_var($value, FILTER_SANITIZE_NUMBER_INT)) {
            return (int) $res;
        }
    }

    /**
     * @param int|string $value
     *
     * @return string
     */
    protected function sanitizeString($value)
    {
        return filter_var((string) $value, FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH);
    }
}
