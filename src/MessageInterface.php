<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use QL\MCP\Common\GUID;
use QL\MCP\Common\Time\TimePoint;

/**
 * The standard QL log message for structured logging.
 */
interface MessageInterface
{
    const ID = 'id';
    const MESSAGE = 'message';
    const SEVERITY = 'severity';
    const CREATED = 'created';

    const CONTEXT = 'context';
    const DETAILS = 'details';

    const APPLICATION_ID = 'applicationID';

    const SERVER_ENVIRONMENT = 'serverEnvironment';
    const SERVER_IP = 'serverIP';
    const SERVER_HOSTNAME = 'serverHostname';

    const REQUEST_METHOD = 'requestMethod';
    const REQUEST_URL = 'requestURL';

    const USER_AGENT = 'userAgent';
    const USER_IP = 'userIP';

    /**
     * Get all data for this message.
     */
    public function all(): array;

    /**
     * Message details - Random unique identifier for this message.
     */
    public function id(): GUID;

    /**
     * Message details - A brief human readable log message.
     */
    public function message(): string;

    /**
     * Message details - Severity or "log level" of message.
     */
    public function severity(): string;

    /**
     * Message details - The time the message was created.
     */
    public function created(): TimePoint;

    /**
     * Message details - Application-specific or additional properties attached to the message.
     */
    public function context(): array;

    /**
     * Message details - Additional information for the message (May contain newlines or large amounts of text)
     */
    public function details(): string;

    /**
     * Application details - Unique identifier for this application.
     */
    public function applicationID(): ?string;

    /**
     * Server details - Environment the server is deployed within.
     */
    public function serverEnvironment(): ?string;

    /**
     * Server details - IP address of machine.
     */
    public function serverIP(): ?string;

    /**
     * Server details - Human readable hostname of machine.
     */
    public function serverHostname(): ?string;

    /**
     * Request details - HTTP Method of endpoint.
     */
    public function requestMethod(): ?string;

    /**
     * Request details - Full URL of endpoint.
     */
    public function requestURL(): ?string;

    /**
     * User details - User agent of client.
     */
    public function userAgent(): ?string;

    /**
     * User details - IP of client.
     */
    public function userIP(): ?string;
}
