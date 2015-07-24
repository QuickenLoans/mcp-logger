<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Message\RequestInterface;
use MCP\Logger\Exception;
use MCP\Logger\MessageInterface;
use Exception as BaseException;

/**
 * @internal
 */
trait GuzzleTrait
{
    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @param MessageInterface $message
     *
     * @return RequestInterface
     */
    protected function createRequest(MessageInterface $message)
    {
        $options = [
            'body' => call_user_func($this->renderer, $message),
            'headers' => ['Content-Type' => $this->renderer->contentType()],
            'exceptions' => true
        ];

        return $this->guzzle->createRequest('POST', $this->uri->expand([]), $options);
    }

    /**
     * Requires Guzzle 5
     *
     * @param RequestInterface[] $requests
     *
     * @return null
     */
    protected function handleBatch(array $requests)
    {
        $errors = [];

        foreach ($requests as $request) {
            try {
                $response = $this->guzzle->send($request);
            } catch (BaseException $e) {
                $errors[] = $e;
            }
        }

        $this->handleErrors($requests, $errors);
    }

    /**
     * Requires Guzzle 5
     *
     * @param RequestInterface[] $requests
     * @param ErrorEvent[] $errors
     *
     * @throws Exception
     *
     * @return null
     */
    protected function handleErrors(array $requests, array $errors)
    {
        if (!$errors) {
            return;
        }

        $batchSize = count($requests);

        $template = defined('static::ERR_BATCH') ? static::ERR_BATCH : '%d errors occured while sending %d messages with mcp-logger';
        $msg = sprintf($template, count($errors), $batchSize);

        // Silent handling
        if (property_exists($this, 'isSilent') && $this->isSilent) {
            error_log($msg);
            return;
        }

        // Send a more specific message if only one error
        if ($batchSize === 1) {
            $e = reset($errors);
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        throw new Exception($msg);
    }
}
