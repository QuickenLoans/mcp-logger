<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use GuzzleHttp\ClientInterface;
use MCP\Logger\Exception;
use MCP\Logger\MessageInterface;
use MCP\Logger\ServiceInterface;
use QL\UriTemplate\UriTemplate;

/**
 * Http Service for Guzzle 5.
 *
 * This service is capable of buffering messages and sending them all at once at the end of a request.
 */
class Guzzle5Service implements ServiceInterface
{
    use BufferedServiceTrait;
    use GuzzleTrait;

    /**
     * @var string
     */
    const ERR_GUZZLE_5_REQUIRED = 'Guzzle 5 and GuzzleHttp\Pool are required to use this service.';
    const ERR_BATCH = '%d Errors occured while sending %d messages with mcp-logger';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UriTemplate
     */
    private $uri;

    /**
     * @var boolean
     */
    private $isSilent;

    /**
     * @param ClientInterface $guzzle
     * @param SerializerInterface $serializer
     * @param UriTemplate $uri
     * @param bool|true $isSilent
     * @param bool|true $enableshutDownHandler
     * @param int $bufferLimit
     *
     * @throws Exception
     */
    public function __construct(
        ClientInterface $guzzle,
        SerializerInterface $serializer,
        UriTemplate $uri,
        $isSilent = true,
        $enableshutDownHandler = true,
        $bufferLimit = 0
    ) {
        $this->guzzle = $guzzle;
        $this->serializer = $serializer;
        $this->uri = $uri;

        $this->isSilent = (bool) $isSilent;

        $this->validateVersion();

        $this->initializeBuffer($bufferLimit, $enableshutDownHandler);
    }

    /**
     * @param MessageInterface $message
     *
     * @return void
     */
    public function send(MessageInterface $message)
    {
        $this->append($message);
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    private function validateVersion()
    {
        if (defined('GuzzleHttp\ClientInterface::VERSION')) {
            $majorVersion = (int) substr(ClientInterface::VERSION, 0, 1);
            if ($majorVersion === 5) {
                return;
            }
        }

        throw new Exception(self::ERR_GUZZLE_5_REQUIRED);
    }
}
