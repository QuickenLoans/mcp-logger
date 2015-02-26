<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Pool;
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
    /**
     * @type string
     */
    const ERR_RESPONSE_CODE = "The service responded with an unexpected http code: '%s'.";
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
     * @type int
     */
    private $bufferLimit;

    /**
     * @type int
     */
    private $buffer;

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

        $this->buffer = [];
        $this->bufferLimit = (int) $bufferLimit;

        if (!class_exists('GuzzleHttp\Pool')) {
            throw new Exception(self::ERR_GUZZLE_5_REQUIRED);
        }

        if ($enableshutDownHandler) {
            // register to run on shutdown
            register_shutdown_function([$this, 'flush']);
        }
    }

    /**
     * @param MessageInterface $message
     * @return null
     */
    public function send(MessageInterface $message)
    {
        $this->buffer[] = $message;

        $this->checkBuffer();
    }

    /**
     * Flush and send all messages in the buffer.
     *
     * By default this is called through an automatically attached shutdown handler.
     *
     * Alternatively, you can disable the shutdown handler and call it manually in your own shutdown or error handler.
     *
     * @return null
     */
    public function flush()
    {
        if (!$this->buffer) {
            return;
        }

        $messages = $this->buffer;
        $this->buffer = [];

        // Convert messages to Guzzle requests
        array_walk($messages, function(&$message) {
            $message = $this->createRequest($message);
        });

        $this->handleBatch($messages);
    }

    /**
     * Check if the buffer limit has been breached and flush messages if so.
     *
     * @return null
     */
    private function checkBuffer()
    {
        if (count($this->buffer) <= $this->bufferLimit) {
            return;
        }

        $this->flush();
    }

    /**
     * @param MessageInterface $message
     *
     * @return RequestInterface
     */
    protected function createRequest(MessageInterface $message)
    {
        $options = [
            'body' => call_user_func($this->renderer, $message),
            'headers' => ['Content-Type' => 'text/xml']
        ];

        return $this->guzzle->createRequest('POST', $this->uri->expand([]), $options);
    }

    /**
     * @param RequestInterface[] $requests
     *
     * @return null
     */
    protected function handleBatch(array $requests)
    {
        $errors = [];

        Pool::send($this->guzzle, $requests, [
            'error' => function (ErrorEvent $event) use (&$errors) {
                $errors[] = $event;
            }
        ]);

        if ($errors) {
            $this->handleErrors(count($requests), $errors);
        }
    }

    /**
     * @param int $batchSize
     * @param array $errors
     *
     * @return null
     */
    private function handleErrors($batchSize, array $errors)
    {
        $msg = sprintf(static::ERR_BATCH, count($errors), $batchSize);

        if ($this->isSilent) {
            error_log($msg);
            return;
        }

        if ($batchSize === 1) {
            $err = reset($errors);
            $ex = $err->getException();

            $ex = new Exception($ex->getMessage(), $ex->getCode(), $ex);

        } else {
            $ex = new Exception($msg);
        }

        throw $ex;
    }
}
