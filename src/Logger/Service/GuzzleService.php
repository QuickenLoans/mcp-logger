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

/**
 * @internal
 */
class GuzzleService implements ServiceInterface
{
    /**#@+
     * @var string
     */
    const ERR_MISSING_URL = 'The Http Client is missing a base url.';
    const ERR_RESPONSE_CODE = "The service responded with an unexpected http code: '%s'.";
    /**#@-*/

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var boolean
     */
    private $isSilent;

    /**
     * @param ClientInterface $client
     * @param RendererInterface $renderer
     * @param boolean $isSilent
     */
    public function __construct(ClientInterface $client, RendererInterface $renderer, $isSilent = false)
    {
        $this->client = $client;
        $this->renderer = $renderer;
        $this->isSilent = $isSilent;

        if (!$this->client->getBaseUrl()) {
            throw new Exception(self::ERR_MISSING_URL);
        }
    }

    /**
     * @param MessageInterface $message
     * @return null
     */
    public function send(MessageInterface $message)
    {
        $request = $this->client->post(
            null,
            null,
            call_user_func($this->renderer, $message),
            ['Content-Type' => 'text/xml']
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
