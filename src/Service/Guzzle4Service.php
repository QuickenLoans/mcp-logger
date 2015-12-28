<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
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
 * DEPRECATED. This service has been deprecated. You should instead use the Http Service which leverages
 * MCP Http for sending messages and can support Guzzle 4, 5, or 6.
 *
 * @deprecated
 * @internal
 */
class Guzzle4Service implements ServiceInterface
{
    use GuzzleTrait;

    /**
     * @type string
     */
    const ERR_RESPONSE_CODE = "The service responded with an unexpected http code: '%s'.";

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
        $request = $this->createRequest($message);

        try {
            $this->guzzle->send($request);
        } catch (TransferException $ex) {
            $this->handleError($ex);
        }
    }

    /**
     * @param TransferException $ex
     *
     * @return null
     */
    private function handleError(TransferException $ex)
    {
        if ($this->isSilent) {
            error_log($ex->getMessage());
            return;
        }

        throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
    }
}
