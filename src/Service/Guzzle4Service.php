<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use MCP\Logger\Exception;
use MCP\Logger\MessageInterface;
use MCP\Logger\RendererInterface;
use MCP\Logger\ServiceInterface;
use QL\UriTemplate\UriTemplate;

/**
 * Http Service for Guzzle 4.
 *
 * @internal
 */
class Guzzle4Service implements ServiceInterface
{
    /**
     * @type string
     */
    const ERR_RESPONSE_CODE = "The service responded with an unexpected http code: '%s'.";

    /**
     * @type ClientInterface
     */
    private $guzzle;

    /**
     * @type RendererInterface
     */
    private $renderer;

    /**
     * @type UriTemplate
     */
    private $uri;

    /**
     * @type boolean
     */
    private $isSilent;

    /**
     * @param ClientInterface $guzzle
     * @param RendererInterface $renderer
     * @param UriTemplate $uri
     * @param boolean $isSilent
     */
    public function __construct(
        ClientInterface $guzzle,
        RendererInterface $renderer,
        UriTemplate $uri,
        $isSilent = true
    ) {
        $this->guzzle = $guzzle;
        $this->renderer = $renderer;
        $this->uri = $uri;

        $this->isSilent = $isSilent;
    }

    /**
     * @param MessageInterface $message
     * @return null
     */
    public function send(MessageInterface $message)
    {
        $options = [
            'body' => call_user_func($this->renderer, $message),
            'headers' => ['Content-Type' => 'text/xml']
        ];

        $request = $this->guzzle->createRequest('POST', $this->uri->expand([]), $options);

        if ($this->isSilent) {
            return $this->fireAndForget($request);
        }

        $response = $this->guzzle->send($request);

        // Guzzle 4 = string, Guzzle 5 = int
        $status = (int) $response->getStatusCode();
        if ($status !== 200) {
            throw new Exception(sprintf(self::ERR_RESPONSE_CODE, $response->getStatusCode()));
        }
    }

    /**
     * @param RequestInterface $request
     * @return null
     */
    private function fireAndForget(RequestInterface $request)
    {
        try {
            $this->guzzle->send($request);

        } catch (TransferException $e) {
            error_log($e->getMessage());
        }
    }
}
