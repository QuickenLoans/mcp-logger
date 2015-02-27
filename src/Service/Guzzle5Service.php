<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use GuzzleHttp\ClientInterface;
use MCP\Logger\Exception;
use MCP\Logger\MessageInterface;
use MCP\Logger\RendererInterface;
use MCP\Logger\ServiceInterface;
use QL\UriTemplate\UriTemplate;

/**
 * Http Service for Guzzle 5.
 *
 * This service is capable of buffering messages and sending them all at once at the end of a request.
 *
 * @internal
 */
class Guzzle5Service implements ServiceInterface
{
    use BufferedServiceTrait;
    use GuzzleTrait;

    /**
     * @type string
     */
    const ERR_GUZZLE_5_REQUIRED = 'Guzzle 5 and GuzzleHttp\Pool are required to use this service.';
    const ERR_BATCH = '%d Errors occured while sending %d messages with mcp-logger';

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
     * @param boolean $enableshutDownHandler
     * @param int $bufferLimit
     */
    public function __construct(
        ClientInterface $guzzle,
        RendererInterface $renderer,
        UriTemplate $uri,
        $isSilent = true,
        $enableshutDownHandler = true,
        $bufferLimit = 0
    ) {
        $this->guzzle = $guzzle;
        $this->renderer = $renderer;
        $this->uri = $uri;

        $this->isSilent = (bool) $isSilent;

        if (!class_exists('GuzzleHttp\Pool')) {
            throw new Exception(self::ERR_GUZZLE_5_REQUIRED);
        }

        $this->initializeBuffer($bufferLimit, $enableshutDownHandler);
    }

    /**
     * @param MessageInterface $message
     *
     * @return null
     */
    public function send(MessageInterface $message)
    {
        $this->append($message);
    }
}
