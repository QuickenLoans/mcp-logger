<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use MCP\Logger\MessageInterface;

/**
 * Requires the following methods to be provided:
 * - createRequest($message)
 * - handleBatch($requests)
 */
trait BufferedServiceTrait
{
    /**
     * @var int
     */
    private $bufferLimit;

    /**
     * @var MessageInterface[]
     */
    private $buffer;

    /**
     * @param int $bufferLimit
     * @param bool $useShutDownHandler
     *
     * @return null
     */
    private function initializeBuffer($bufferLimit = 0, $useShutDownHandler = true)
    {
        $this->buffer = [];
        $this->bufferLimit = (int) $bufferLimit;

        if ($useShutDownHandler) {
            register_shutdown_function([$this, 'flush']);
        }
    }

    /**
     * @param MessageInterface $message
     * @return null
     */
    private function append(MessageInterface $message)
    {
        $this->buffer[] = $message;

        $this->checkBuffer();
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

        // Convert messages to requests
        array_walk($messages, function(&$message) {
            $message = $this->createRequest($message);
        });

        $this->handleBatch($messages);
    }
}
