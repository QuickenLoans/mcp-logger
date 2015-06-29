<?php

namespace MCP\Logger\Service;

use Aws\Kinesis\KinesisClient;
use MCP\Logger\RendererInterface;
use MCP\Logger\ServiceInterface;
use MCP\Logger\MessageInterface;
use MCP\Logger\Exception;

/**
 * Logging service for Amazon Kinesis
 *
 * The class has the ability to buffer messages and send them all at the end of the request.
 */
class KinesisService implements ServiceInterface
{
    use BufferedServiceTrait;

    const ERR_BUFFER = 'Buffer size must be between 0 and 499.';
    const ERR_RESPONSE = 'Received improperly formatted response when sending messages.';
    const ERR_BATCH = 'Unable to send %s (of %s) log messages after %s attempts. Check that the Kinesis stream exists and has enough shards.';

    const STREAM_DEFAULT = 'Logger';
    const ATTEMPTS_DEFAULT = 5;

    /**
     * @var KinesisClient
     */
    private $client;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var string
     */
    private $stream;

    /**
     * @var bool
     */
    private $silent;

    /**
     * @var int
     */
    private $attempts;

    /**
     * @param KinesisClient $client
     * @param RendererInterface $renderer
     * @param string $stream
     * @param bool $silent
     * @param int $attempts
     * @param bool $shutdownHandler
     * @param int $bufferLimit
     * @throws Exception
     */
    public function __construct(
        KinesisClient $client,
        RendererInterface $renderer,
        $stream = self::STREAM_DEFAULT,
        $silent = true,
        $attempts = self::ATTEMPTS_DEFAULT,
        $shutdownHandler = true,
        $bufferLimit = 0
    ) {
        $this->client = $client;
        $this->renderer = $renderer;
        $this->stream = $stream;
        $this->silent = $silent;
        $this->attempts = $attempts;

        if ($bufferLimit < 0 || $bufferLimit > 499) {
            throw new Exception(self::ERR_BUFFER);
        }

        $this->initializeBuffer($bufferLimit, $shutdownHandler);
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message)
    {
        $this->append($message);
    }

    /**
     * @param MessageInterface $message
     * @return array
     */
    private function createRequest(MessageInterface $message)
    {
        $data = call_user_func($this->renderer, $message);

        return [
            'Data' => base64_encode($data),
            'PartitionKey' => hash('sha256', mt_rand() . $data)
        ];
    }

    /**
     * @param array $messages
     * @return void
     * @throws Exception
     */
    private function handleBatch(array $messages)
    {
        $total = count($messages);
        $attempts = 0;

        do {

            echo "\n\nPREPARING TO SEND (".$attempts.")\n\n";
            var_dump([
                'Records' => $messages,
                'StreamName' => $this->stream
            ]);

            $result = $this->client->putRecords([
                'Records' => $messages,
                'StreamName' => $this->stream
            ]);

            if (!isset($result['Records']) || !is_array($result['Records'])) {
                throw new Exception(self::ERR_RESPONSE);
            }

            $messages = array_values(array_intersect_key($messages, array_filter($result['Records'], function (array $message) {
                // filter out messages that were successfully sent
                return (isset($message['SequenceNumber']) && isset($message['ShardId'])) ? false : true;
            })));

        } while (count($messages) > 0 && $attempts++ < $this->attempts);

        // handle errors
        if (count($messages) > 0) {
            $this->error(sprintf(self::ERR_BATCH, count($messages), $total, $attempts));
        }
    }

    /**
     * Handle errors
     *
     * @param string $message
     * @throws Exception
     */
    private function error($message)
    {
        if ($this->silent) {
            error_log($message);
        } else {
            throw new Exception($message);
        }
    }
}