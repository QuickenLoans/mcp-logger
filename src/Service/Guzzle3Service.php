<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use Guzzle\Common\Exception\GuzzleException;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\RequestInterface;
use MCP\Logger\Exception;
use MCP\Logger\MessageInterface;
use MCP\Logger\RendererInterface;
use MCP\Logger\ServiceInterface;
use QL\UriTemplate\UriTemplate;

/**
 * Http Service for Guzzle 3.
 *
 * DEPRECATED. This service has been deprecated. You should instead use the Http Service which leverages
 * MCP Http for sending messages and can support Guzzle 4, 5, or 6.
 *
 * @deprecated
 * @internal
 */
class Guzzle3Service implements ServiceInterface
{
    /**
     * @type string
     */
    const ERR_RESPONSE_CODE = "The service responded with an unexpected http code: '%s'.";

    /**
     * @type ClientInterface
     */
    private $service;

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
     * @param ClientInterface $service
     * @param RendererInterface $renderer
     * @param UriTemplate $uri
     * @param boolean $isSilent
     */
    public function __construct(
        ClientInterface $service,
        RendererInterface $renderer,
        UriTemplate $uri,
        $isSilent = true
    ) {
        $this->service = $service;
        $this->renderer = $renderer;
        $this->uri = $uri;
        $this->isSilent = $isSilent;
    }

    /**
     * @param MessageInterface $message
     *
     * @throws Exception
     *
     * @return void
     */
    public function send(MessageInterface $message)
    {
        $request = $this->service->post(
            $this->uri->expand([]),
            ['Content-Type' => 'text/xml'],
            call_user_func($this->renderer, $message)
        );

        if ($this->isSilent) {
            return $this->fireAndForget($request);
        }

        $response = $request->send();

        if ($response->getStatusCode() !== 200) {
            throw new Exception(sprintf(self::ERR_RESPONSE_CODE, $response->getStatusCode()));
        }
    }

    /**
     * @param RequestInterface $request
     *
     * @return void
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
