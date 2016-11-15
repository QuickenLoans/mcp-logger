<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Message;

use MCP\Logger\MessageInterface;
use Psr\Log\LogLevel;
use QL\MCP\Common\GUID;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\TimePoint;

/**
 * This class represents a basic structured log message.
 *
 * The following properties are required, but will be populated with defaults if missing:
 *
 * - id             : Random GUID
 * - severity       : info
 * - context        : []
 *
 * The following properties are required:
 *
 * - message
 * - created        : TimePoint (from MessageFactory)
 * - applicationID  : (from MessageFactory)
 * - serverIP       : IPv4Address (from MessageFactory)
 * - serverHostname : (from MessageFactory)
 *
 * The following properties are not required:
 *
 * - errorDetails
 * - serverEnvironment
 * - requestMethod
 * - requestURL
 * - userAgent
 * - userIP
 * - userName
 */

class Message implements MessageInterface
{
    use MessageLoadingTrait;

    /**
     * @var GUID
     */
    private $id;

    /**
     * @var string
     */
    private $message;
    private $severity;
    private $errorDetails;

    /**
     * @var array
     */
    private $context;

    /**
     * @var TimePoint
     */
    private $created;

    /**
     * @var string
     */
    private $applicationID;

    /**
     * @var string
     */
    private $serverEnvironment;
    private $serverHostname;

    /**
     * @var IPv4Address|null
     */
    private $serverIP;

    /**
     * @var string|null
     */
    private $requestMethod;
    private $requestURL;

    /**
     * @var string|null
     */
    private $userAgent;
    private $userName;

    /**
     * @var IPv4Address|null
     */
    private $userIP;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        // Required, will get a default value if not provided.
        $this->id = $this->parseClass(MessageInterface::ID, $data, GUID::class, function() {
            return GUID::create();
        });

        $this->severity = $this->parseLevel(MessageInterface::SEVERITY, $data, LogLevel::INFO);
        $this->context = $this->parseContext(MessageInterface::CONTEXT, $data, []);

        // Required, no default provided
        $this->message = $this->parseRequiredValue(MessageInterface::MESSAGE, $data);
        $this->created = $this->parseRequiredClass(MessageInterface::CREATED, $data, TimePoint::class);

        $this->applicationID = $this->parseRequiredValue(MessageInterface::APPLICATION_ID, $data);

        $this->serverIP = $this->parseRequiredClass(MessageInterface::SERVER_IP, $data, IPv4Address::class);
        $this->serverHostname = $this->parseRequiredValue(MessageInterface::SERVER_HOSTNAME, $data);

        // Not required
        $this->errorDetails = $this->parseValue(MessageInterface::ERROR_DETAILS, $data);

        $this->serverEnvironment = $this->parseValue(MessageInterface::SERVER_ENVIRONMENT, $data);

        $this->requestMethod = $this->parseValue(MessageInterface::REQUEST_METHOD, $data);
        $this->requestURL = $this->parseValue(MessageInterface::REQUEST_URL, $data);

        $this->userAgent = $this->parseValue(MessageInterface::USER_AGENT, $data);
        $this->userName = $this->parseValue(MessageInterface::USER_NAME, $data);
        $this->userIP = $this->parseClass(MessageInterface::USER_IP, $data, IPv4Address::class);
    }

    /**
     * @return GUID
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function severity()
    {
        return $this->severity;
    }

    /**
     * @return array
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * @return string|null
     */
    public function errorDetails()
    {
        return $this->errorDetails;
    }

    /**
     * @return TimePoint
     */
    public function created()
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function applicationID()
    {
        return $this->applicationID;
    }

    /**
     * @return string|null
     */
    public function serverEnvironment()
    {
        return $this->serverEnvironment;
    }

    /**
     * @return string
     */
    public function serverHostname()
    {
        return $this->serverHostname;
    }

    /**
     * @return IPv4Address
     */
    public function serverIP()
    {
        return $this->serverIP;
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
    public function requestURL()
    {
        return $this->requestURL;
    }

    /**
     * @return string|null
     */
    public function userAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return IPv4Address|null
     */
    public function userIP()
    {
        return $this->userIP;
    }

    /**
     * @return string|null
     */
    public function userName()
    {
        return $this->userName;
    }
}
