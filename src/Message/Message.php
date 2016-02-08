<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Message;

use QL\MCP\Common\GUID;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\TimePoint;
use MCP\Logger\LogLevelInterface;
use MCP\Logger\MessageInterface;

/**
 * @internal
 */
class Message implements LogLevelInterface, MessageInterface
{
    use MessageLoadingTrait;

    /**
     * @type string|null
     */
    private $affectedSystem;
    private $applicationId;
    private $categoryId;
    private $environment;
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

    /**
     * @var GUID
     */
    private $id;

    /**
     * @type boolean
     */
    private $isUserDisrupted;

    /**
     * @type TimePoint|null
     */
    private $createTime;

    /**
     * @type IPv4Address|null
     */
    private $machineIPAddress;
    private $userIPAddress;

    /**
     * @type mixed[]
     */
    private $extendedProperties;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        $this->id = $this->parseClassType('id', $data, GUID::class, false, GUID::create());
        $this->applicationId = $this->parseValue('applicationId', $data, true);
        $this->createTime = $this->parseClassType('createTime', $data, TimePoint::class, true);
        $this->machineIPAddress = $this->parseClassType('machineIPAddress', $data, IPv4Address::class, true);
        $this->machineName = $this->parseValue('machineName', $data, true);
        $this->message = $this->parseValue('message', $data, true);

        $this->environment = $this->parseValue('environment', $data, false);

        $this->extendedProperties = $this->parseProperties('extendedProperties', $data, false, []);
        $this->level = $this->parseLevel('level', $data, false, static::INFO);
        $this->isUserDisrupted = $this->parseBoolean('isUserDisrupted', $data, false);

        $this->affectedSystem = $this->parseValue('affectedSystem', $data);
        $this->categoryId = $this->parseValue('categoryId', $data);
        $this->exceptionData = $this->parseValue('exceptionData', $data);
        $this->referrer = $this->parseValue('referrer', $data);
        $this->requestMethod = $this->parseValue('requestMethod', $data);
        $this->url = $this->parseValue('url', $data);
        $this->userAgentBrowser = $this->parseValue('userAgentBrowser', $data);
        $this->userCommonId = $this->parseValue('userCommonId', $data, false, null);
        $this->userDisplayName = $this->parseValue('userDisplayName', $data);
        $this->userName = $this->parseValue('userName', $data);
        $this->userScreenName = $this->parseValue('userScreenName', $data);

        $this->userIPAddress = $this->parseClassType('userIPAddress', $data, IPv4Address::class);
    }

    /**
     * @return GUID
     */
    public function id()
    {
        return $this->id;
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
    public function environment()
    {
        return $this->environment;
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
