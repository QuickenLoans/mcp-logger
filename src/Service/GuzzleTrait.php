<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use GuzzleHttp\Message\RequestInterface;
use MCP\Logger\MessageInterface;

/**
 * @internal
 */
trait GuzzleTrait
{
    /**
     * @param MessageInterface $message
     *
     * @return RequestInterface
     */
    protected function createRequest(MessageInterface $message)
    {
        $options = [
            'body' => call_user_func($this->renderer, $message),
            'headers' => ['Content-Type' => 'text/xml'],
            'exceptions' => true
        ];

        return $this->guzzle->createRequest('POST', $this->uri->expand([]), $options);
    }
}
