<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Service;

use MCP\Service\Logger\Exception;
use HttpException;
use HttpRequest;
use MCP\Service\Logger\MessageInterface;
use MCP\Service\Logger\RendererInterface;
use MCP\Service\Logger\ServiceInterface;

/**
 * @internal
 */
class HttpService implements ServiceInterface
{
    /**#@+
     * @var string
     */
    const ERR_MISSING_URL = 'The HttpRequest object is missing a url.';
    const ERR_RESPONSE_CODE = "The service responded with an unexpected http code: '%s'.";
    /**#@-*/

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var boolean
     */
    private $isSilent;

    /**
     * @param HttpRequest $request
     * @param RendererInterface $renderer
     * @param boolean $isSilent
     */
    public function __construct(HttpRequest $request, RendererInterface $renderer, $isSilent = false)
    {
        $this->request = $request;
        $this->renderer = $renderer;
        $this->isSilent = $isSilent;

        if (!$this->request->getUrl()) {
            throw new Exception(self::ERR_MISSING_URL);
        }
    }

    /**
     * @param MessageInterface $message
     * @throws HttpException
     * @return null
     */
    public function send(MessageInterface $message)
    {
        $request = clone $this->request;
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
