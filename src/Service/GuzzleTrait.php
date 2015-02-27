<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Pool;
use MCP\Logger\Exception;
use MCP\Logger\MessageInterface;

/**
 * @internal
 */
trait GuzzleTrait
{
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

        Pool::send($this->guzzle, $requests, [
            'error' => function (ErrorEvent $event) use (&$errors) {
                $errors[] = $event;
            }
        ]);

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
            $err = reset($errors);
            if ($err instanceof ErrorEvent) {
                $ex = $err->getException();
                throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
            }
        }

        throw new Exception($msg);
    }
}
