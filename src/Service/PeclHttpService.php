<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use HttpException;
use HttpRequest;
use MCP\Logger\Exception;
use MCP\Logger\MessageInterface;
use MCP\Logger\RendererInterface;
use MCP\Logger\ServiceInterface;
use QL\UriTemplate\UriTemplate;

/**
 * Http Service for Pecl HTTP 1.*.
 *
 * @internal
 */
class PeclHttpService implements ServiceInterface
{
    /**#@+
     * @type string
     */
    const ERR_RESPONSE_CODE = "The service responded with an unexpected http code: '%s'.";
    /**#@-*/

    /**
     * @type HttpRequest
     */
    private $request;

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
     * @param HttpRequest $request
     * @param RendererInterface $renderer
     * @param UriTemplate $uri
     * @param boolean $isSilent
     */
    public function __construct(
        HttpRequest $request,
        RendererInterface $renderer,
        UriTemplate $uri,
        $isSilent = false
    ) {
        $this->request = $request;
        $this->renderer = $renderer;
        $this->uri = $uri;

        $this->isSilent = $isSilent;
    }

    /**
     * @param MessageInterface $message
     * @throws HttpException
     * @return null
     */
    public function send(MessageInterface $message)
    {
        $request = clone $this->request;
        $request->setUrl($this->uri->expand([]));
        $request->setMethod(HttpRequest::METH_POST);
        $request->setBody(call_user_func($this->renderer, $message));
        $request->setContentType('text/xml');

        if ($this->isSilent) {
            $this->fireAndForget($request);

        } else {
            $response = $request->send();
            if ($response->getResponseCode() !== 200) {
                throw new Exception(sprintf(self::ERR_RESPONSE_CODE, $response->getResponseCode()));
            }
        }
    }

    /**
     * @param HttpRequest $request
     * @return null
     */
    private function fireAndForget(HttpRequest $request)
    {
        try {
            $request->send();

        } catch (HttpException $e) {
            error_log($e->getMessage());
        }
    }
}
