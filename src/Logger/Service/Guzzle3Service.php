<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Service;

use Guzzle\Common\Exception\GuzzleException;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\RequestInterface;
use MCP\Service\Logger\Exception;
use MCP\Service\Logger\MessageInterface;
use MCP\Service\Logger\RendererInterface;
use MCP\Service\Logger\ServiceInterface;
use QL\UriTemplate\UriTemplate;

/**
 * Http Service for Guzzle 3.
 *
 * @internal
 */
class Guzzle3Service implements ServiceInterface
{
    /**#@+
     * @type string
     */
    const ERR_RESPONSE_CODE = "The service responded with an unexpected http code: '%s'.";
    /**#@-*/

    /**
     * @type ClientInterface
     */
    private $client;

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
     * @param ClientInterface $client
     * @param RendererInterface $renderer
     * @param UriTemplate $uri
     * @param boolean $isSilent
     */
    public function __construct(
        ClientInterface $client,
        RendererInterface $renderer,
        UriTemplate $uri,
        $isSilent = false
    ) {
        $this->client = $client;
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
        $request = $this->client->post(
            $this->uri->expand([]),
            ['Content-Type' => 'text/xml'],
            call_user_func($this->renderer, $message)
        );

        if ($this->isSilent) {
            $this->fireAndForget($request);

        } else {
            $response = $request->send();
            if ($response->getStatusCode() !== 200) {
                throw new Exception(sprintf(self::ERR_RESPONSE_CODE, $response->getStatusCode()));
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @return null
     */
    private function fireAndForget(RequestInterface $request)
    {
        try {
            $request->send();

        } catch (GuzzleException $e) {
            error_log($e->getMessage());
        }
    }
}
