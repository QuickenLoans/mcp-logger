<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Message;

use MCP\DataType\IPv4Address;
use MCP\DataType\Time\TimePoint;
use MCP\Service\Logger\LogLevelInterface;
use MCP\Service\Logger\MessageInterface;

/**
 * @internal
 */
class Message implements LogLevelInterface, MessageInterface
{
    use MessageLoadingTrait;

    /**#@+
     * @var string|null
     */
    private $affectedSystem;
    private $applicationId;
    private $categoryId;
    private $exceptionData;
    private $level;
    private $machineName;
    private $message;
    private $referrer;
    private $requestMethod;
    private $url;
    private $userAgentBrowser;
    private $userCommonId;
    private $userDisplayName;
    private $userName;
    private $userScreenName;
    /**#@-*/

    /**#@+
     * @var boolean
     */
    private $isUserDisrupted;

    /**#@+
     * @var TimePoint|null
     */
    private $createTime;
    /**#@-*/

    /**#@+
     * @var IPv4Address|null
     */
    private $machineIPAddress;
    private $userIPAddress;
    /**#@-*/

    /**
     * @var mixed[]
     */
    private $extendedProperties;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        $this->applicationId = $this->parseValue('applicationId', $data, true);
        $this->createTime = $this->parseClassType('createTime', $data, 'MCP\DataType\Time\TimePoint', true);
        $this->machineIPAddress = $this->parseClassType('machineIPAddress', $data, 'MCP\DataType\IPv4Address', true);
        $this->machineName = $this->parseValue('machineName', $data, true);
        $this->message = $this->parseValue('message', $data, true);

        $this->extendedProperties = $this->parseProperties('extendedProperties', $data, false, array());
        $this->level = $this->parseLevel('level', $data, false, static::INFO);
        $this->isUserDisrupted = $this->parseBoolean('isUserDisrupted', $data, false);

        $this->affectedSystem = $this->parseValue('affectedSystem', $data);
        $this->categoryId = $this->parseValue('categoryId', $data);
        $this->exceptionData = $this->parseValue('exceptionData', $data);
        $this->referrer = $this->parseValue('referrer', $data);
        $this->requestMethod = $this->parseValue('requestMethod', $data);
        $this->url = $this->parseValue('url', $data);
        $this->userAgentBrowser = $this->parseValue('userAgentBrowser', $data);
        $this->userCommonId = $this->parseValue('userCommonId', $data, false, 0);
        $this->userDisplayName = $this->parseValue('userDisplayName', $data);
        $this->userName = $this->parseValue('userName', $data);
        $this->userScreenName = $this->parseValue('userScreenName', $data);

        $this->userIPAddress = $this->parseClassType('userIPAddress', $data, 'MCP\DataType\IPv4Address');
    }

    /**
     * @return string|null
     */
    public function affectedSystem()
    {
        return $this->affectedSystem;
    }

    /**
     * @return string|null
     */
    public function applicationId()
    {
        return $this->applicationId;
    }

    /**
     * @return string|null
     */
    public function categoryId()
    {
        return $this->categoryId;
    }

    /**
     * @return TimePoint|null
     */
    public function createTime()
    {
        return $this->createTime;
    }

    /**
     * @return string|null
     */
    public function exceptionData()
    {
        return $this->exceptionData;
    }

    /**
     * @return array|null
     */
    public function extendedProperties()
    {
        return $this->extendedProperties;
    }

    /**
     * @return string|null
     */
    public function isUserDisrupted()
    {
        return $this->isUserDisrupted;
    }

    /**
     * @return string|null
     */
    public function level()
    {
        return $this->level;
    }

    /**
     * @return IPv4Address|null
     */
    public function machineIPAddress()
    {
        return $this->machineIPAddress;
    }

    /**
     * @return string|null
     */
    public function machineName()
    {
        return $this->machineName;
    }

    /**
     * @return string|null
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function referrer()
    {
        return $this->referrer;
    }

    /**
     * @return string|null
     */
    public function requestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * @return string|null
     */
    public function url()
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function userAgentBrowser()
    {
        return $this->userAgentBrowser;
    }

    /**
     * @return string|null
     */
    public function userCommonId()
    {
        return $this->userCommonId;
    }

    /**
     * @return string|null
     */
    public function userDisplayName()
    {
        return $this->userDisplayName;
    }

    /**
     * @return IPv4Address|null
     */
    public function userIPAddress()
    {
        return $this->userIPAddress;
    }

    /**
     * @return string|null
     */
    public function userName()
    {
        return $this->userName;
    }

    /**
     * @return string|null
     */
    public function userScreenName()
    {
        return $this->userScreenName;
    }
}
