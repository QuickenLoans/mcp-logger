<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Message;

use QL\MCP\Common\GUID;
use QL\MCP\Common\Time\TimePoint;
use QL\MCP\Logger\MessageInterface;

/**
 * This class represents a basic structured log message.
 *
 * The following properties are required:
 *
 * - severity
 * - message
 *
 * The following properties are required, but will be populated with defaults if missing:
 *
 * - id             : Random GUID
 * - created        : TimePoint
 * - context        : []
 * - details        : ''
 *
 * The following properties are not required:
 *
 * - applicationID  : (from MessageFactory)
 * - serverIP       : (from MessageFactory)
 * - serverHostname : (from MessageFactory)
 * - serverEnvironment
 * - requestMethod
 * - requestURL
 * - userAgent
 * - userIP
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
    private $details;

    /**
     * @var TimePoint
     */
    private $created;

    /**
     * @var array
     */
    private $context;

    /**
     * @var string|null
     */
    private $applicationID;

    /**
     * @var string|null
     */
    private $serverEnvironment;
    private $serverHostname;
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
    private $userIP;

    /**
     * @param mixed[] $data
     */
    public function __construct(string $level, string $message, array $data = [])
    {
        $this->severity = $level;
        $this->message = $message;

        // Required, will get a default value if not provided.
        $this->id = $this->parseClass(MessageInterface::ID, $data, GUID::class, function () {
            return GUID::create();
        });

        $this->created = $this->parseClass(MessageInterface::CREATED, $data, TimePoint::class, function () {
            return $this->generateCreatedTime();
        });

        $this->context = $this->parseContext(MessageInterface::CONTEXT, $data, []);
        $this->details = $this->parseValue(MessageInterface::DETAILS, $data, '');

        // Optional values
        $this->applicationID = $this->parseValue(MessageInterface::APPLICATION_ID, $data);

        $this->serverIP = $this->parseValue(MessageInterface::SERVER_IP, $data);
        $this->serverHostname = $this->parseValue(MessageInterface::SERVER_HOSTNAME, $data);

        $this->serverEnvironment = $this->parseValue(MessageInterface::SERVER_ENVIRONMENT, $data);

        $this->requestMethod = $this->parseValue(MessageInterface::REQUEST_METHOD, $data);
        $this->requestURL = $this->parseValue(MessageInterface::REQUEST_URL, $data);

        $this->userAgent = $this->parseValue(MessageInterface::USER_AGENT, $data);
        $this->userIP = $this->parseValue(MessageInterface::USER_IP, $data);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return [
            MessageInterface::SEVERITY => $this->severity(),
            MessageInterface::MESSAGE => $this->message(),

            MessageInterface::ID => $this->id(),
            MessageInterface::CREATED => $this->created(),

            MessageInterface::CONTEXT => $this->context(),
            MessageInterface::DETAILS => $this->details(),

            MessageInterface::APPLICATION_ID => $this->applicationID(),

            MessageInterface::SERVER_ENVIRONMENT => $this->serverEnvironment(),
            MessageInterface::SERVER_IP => $this->serverIP(),
            MessageInterface::SERVER_HOSTNAME => $this->serverHostname(),

            MessageInterface::REQUEST_METHOD => $this->requestMethod(),
            MessageInterface::REQUEST_URL => $this->requestURL(),

            MessageInterface::USER_AGENT => $this->userAgent(),
            MessageInterface::USER_IP => $this->userIP(),
        ];
    }

    /**
     * @return GUID
     */
    public function id(): GUID
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function severity(): string
    {
        return $this->severity;
    }

    /**
     * @return TimePoint
     */
    public function created(): TimePoint
    {
        return $this->created;
    }

    /**
     * @return array
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function details(): string
    {
        return $this->details;
    }

    /**
     * @return string|null
     */
    public function applicationID(): ?string
    {
        return $this->applicationID;
    }

    /**
     * @return string|null
     */
    public function serverEnvironment(): ?string
    {
        return $this->serverEnvironment;
    }

    /**
     * @return string|null
     */
    public function serverHostname(): ?string
    {
        return $this->serverHostname;
    }

    /**
     * @return string|null
     */
    public function serverIP(): ?string
    {
        return $this->serverIP;
    }

    /**
     * @return string|null
     */
    public function requestMethod(): ?string
    {
        return $this->requestMethod;
    }

    /**
     * @return string|null
     */
    public function requestURL(): ?string
    {
        return $this->requestURL;
    }

    /**
     * @return string|null
     */
    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @return string|null
     */
    public function userIP(): ?string
    {
        return $this->userIP;
    }
}
