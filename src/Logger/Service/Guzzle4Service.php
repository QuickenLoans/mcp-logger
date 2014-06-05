<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Message\ResponseInterface;
use MCP\Service\Logger\Exception;
use MCP\Service\Logger\MessageInterface;
use MCP\Service\Logger\RendererInterface;
use MCP\Service\Logger\ServiceInterface;

/**
 * @internal
 */
class Guzzle4Service implements ServiceInterface
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
        $options = [
            'body' => call_user_func($this->renderer, $message),
            'headers' => ['Content-Type' => 'text/xml']
        ];

        if ($this->isSilent) {
            $this->fireAndForget($options);

        } else {
            $response = $this->client->post(null, $options);
            if ($response->getStatusCode() !== '200') {
                throw new Exception(sprintf(self::ERR_RESPONSE_CODE, $response->getStatusCode()));
            }
        }
    }

    /**
     * @param array $requestOptions
     * @return null
     */
    private function fireAndForget(array $requestOptions)
    {
        try {
            $this->client->post(null, $requestOptions);

        } catch (TransferException $e) {
            error_log($e->getMessage());
        }
    }
}
