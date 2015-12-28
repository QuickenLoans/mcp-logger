<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger;

use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\TimePoint;

/**
 * @api
 * @link https://itiki/index.php/Core_Logger
 */
interface MessageInterface
{
    /**
     * @return string|null
     */
    public function affectedSystem();

    /**
     * @return string|null
     */
    public function applicationId();

    /**
     * @return string|null
     */
    public function categoryId();

    /**
     * @return TimePoint|null
     */
    public function createTime();

    /**
     * @return string|null
     */
    public function environment();

    /**
     * @return string|null
     */
    public function exceptionData();

    /**
     * @return array|null
     */
    public function extendedProperties();

    /**
     * @return string|null
     */
    public function isUserDisrupted();

    /**
     * @return string|null
     */
    public function level();

    /**
     * @return IPv4Address|null
     */
    public function machineIPAddress();

    /**
     * @return string|null
     */
    public function machineName();

    /**
     * @return string|null
     */
    public function message();

    /**
     * @return string|null
     */
    public function referrer();

    /**
     * @return string|null
     */
    public function requestMethod();

    /**
     * @return string|null
     */
    public function url();

    /**
     * @return string|null
     */
    public function userAgentBrowser();

    /**
     * @return string|null
     */
    public function userCommonId();

    /**
     * @return string|null
     */
    public function userDisplayName();

    /**
     * @return IPv4Address|null
     */
    public function userIPAddress();

    /**
     * @return string|null
     */
    public function userName();

    /**
     * @return string|null
     */
    public function userScreenName();
}
