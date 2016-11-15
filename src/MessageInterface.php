<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

use QL\MCP\Common\GUID;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\TimePoint;

/**
 * The standard QL log message for structured logging.
 */
interface MessageInterface
{
    const ID = 'id';
    const MESSAGE = 'message';
    const SEVERITY = 'severity';
    const CONTEXT = 'context';
    const ERROR_DETAILS = 'errorDetails';
    const CREATED = 'created';

    const APPLICATION_ID = 'applicationID';

    const SERVER_ENVIRONMENT = 'serverEnvironment';
    const SERVER_IP = 'serverIP';
    const SERVER_HOSTNAME = 'serverHostname';

    const REQUEST_METHOD = 'requestMethod';
    const REQUEST_URL = 'requestURL';

    const USER_AGENT = 'userAgent';
    const USER_IP = 'userIP';
    const USER_NAME = 'userName';

    /**
     * Message details - Random unique identifier for this message.
     *
     * @return GUID
     */
    public function id();

    /**
     * Message details - Human readable log message.
     *
     * @return string
     */
    public function message();

    /**
     * Message details - Severity or "log level" of message.
     *
     * @return string
     */
    public function severity();

    /**
     * Message details - Application-specific or additional properties attached to the message.
     *
     * @return array
     */
    public function context();

    /**
     * Message details - Error-specific or additional information for the error.
     *
     * @return string|null
     */
    public function errorDetails();

    /**
     * Message details - The time the message was created.
     *
     * @return TimePoint
     */
    public function created();

    /**
     * Application details - Unique identifier for this application.
     *
     * @return string
     */
    public function applicationID();

    /**
     * Server details - Environment the server is deployed within.
     *
     * @return string|null
     */
    public function serverEnvironment();

    /**
     * Server details - IP address of machine.
     *
     * @return IPv4Address
     */
    public function serverIP();

    /**
     * Server details - Human readable hostname of machine.
     *
     * @return string
     */
    public function serverHostname();

    /**
     * Request details - HTTP Method of endpoint.
     *
     * @return string|null
     */
    public function requestMethod();

    /**
     * Request details - Full URL of endpoint.
     *
     * @return string|null
     */
    public function requestURL();

    /**
     * User details - User agent of client.
     *
     * @return string|null
     */
    public function userAgent();

    /**
     * User details - IP of client.
     *
     * @return IPv4Address|null
     */
    public function userIP();

    /**
     * User details - Username, display name, or some other identifier for client.
     *
     * @return string|null
     */
    public function userName();
}
