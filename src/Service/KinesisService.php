<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use MCP\Logger\Exception;
use MCP\Logger\RendererInterface;
use MCP\Logger\Renderer\JsonRenderer;
use MCP\Logger\ServiceInterface;
use MCP\Logger\MessageInterface;
use Aws\Kinesis\KinesisClient;
use Aws\Kinesis\Exception\KinesisException;

/**
 * Logging service for Amazon Kinesis
 *
 * The class has the ability to buffer messages and send them all at the end of the request.
 */
class KinesisService implements ServiceInterface
{
    use BufferedServiceTrait;

    // Configuration Keys
    const CONFIG_IS_SILENT = 'silent';
    const CONFIG_BUFFER_LIMIT = 'buffer.limit';
    const CONFIG_KINESIS_ATTEMPTS = 'kinesis.attempts';
    const CONFIG_KINESIS_STREAM = 'kinesis.stream';
    const CONFIG_REGISTER_SHUTDOWN = 'shutdown.register';

    // Configuration Defaults
    const DEFAULT_CONFIG_IS_SILENT = true;
    const DEFAULT_CONFIG_BUFFER_LIMIT = 0;
    const DEFAULT_CONFIG_KINESIS_ATTEMPTS = 5;
    const DEFAULT_CONFIG_KINESIS_STREAM = 'Logger';
    const DEFAULT_CONFIG_REGISTER_SHUTDOWN = true;

    // Error Messages
    const ERR_BUFFER = 'Buffer size must be between 1 and 499.';
    const ERR_ATTEMPTS = 'Number of attempts must be 1 or greater.';
    const ERR_SIZE = 'Log message exceeds 1MB in size. Cannot be sent to Kinesis. Discarding. %s';
    const ERR_UNKNOWN = 'Unknown error when sending messages to Kinesis.';
    const ERR_RESPONSE = 'Received improperly formatted response when sending messages.';
    const ERR_BATCH = 'Unable to send %s (of %s) log messages after %s attempts. Service returned errors. %s';
    const ERR_MULTIPLE = "Encountered %s errors when sending log messages.\n%s";

    const SIZE_MAX = 950000;

    /**
     * @var KinesisClient
     */
    private $client;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param KinesisClient $client
     * @param RendererInterface $renderer
     * @param array $configuration
     *
     * @throws Exception
     */
    public function __construct(
        KinesisClient $client,
        RendererInterface $renderer = null,
        array $configuration = []
    ) {
        $this->client = $client;
        $this->renderer = $renderer ?: new JsonRenderer;

        $this->configuration = array_merge([
            self::CONFIG_IS_SILENT => self::DEFAULT_CONFIG_IS_SILENT,
            self::CONFIG_BUFFER_LIMIT => self::DEFAULT_CONFIG_BUFFER_LIMIT,
            self::CONFIG_KINESIS_ATTEMPTS => self::DEFAULT_CONFIG_KINESIS_ATTEMPTS,
            self::CONFIG_KINESIS_STREAM => self::DEFAULT_CONFIG_KINESIS_STREAM,
            self::CONFIG_REGISTER_SHUTDOWN => self::DEFAULT_CONFIG_REGISTER_SHUTDOWN
        ], $configuration);

        if ($this->configuration[self::CONFIG_KINESIS_ATTEMPTS] < 1) {
            throw new Exception(self::ERR_ATTEMPTS);
        }

        if ($this->configuration[self::CONFIG_BUFFER_LIMIT] < 0 || $this->configuration[self::CONFIG_BUFFER_LIMIT] > 499) {
            throw new Exception(self::ERR_BUFFER);
        }

        $this->initializeBuffer(
            $this->configuration[self::CONFIG_BUFFER_LIMIT],
            $this->configuration[self::CONFIG_REGISTER_SHUTDOWN]
        );
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
     * @param MessageInterface $message
     *
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
     *
     * @throws Exception
     *
     * @return void
     */
    private function handleBatch(array $messages)
    {
        $total = count($messages);
        $errors = [];

        // discard messages over 1MB in size
        $messages = array_filter($messages, function (array $message) use (&$errors) {
            if (strlen($message['Data']) > self::SIZE_MAX) {
                $errors[] = sprintf(self::ERR_SIZE, substr(base64_decode($message['Data']), 0, 500) . '...');
                return false;
            }

            return true;
        });

        $attempts = 0;

        while (count($messages) > 0 && $attempts < $this->configuration[self::CONFIG_KINESIS_ATTEMPTS]) {

            $messages = array_map(function (array $message) {
                return array_intersect_key($message, array_flip(['Data', 'PartitionKey']));
            }, $messages);

            try {
                $results = $this->client->putRecords([
                    'Records' => $messages,
                    'StreamName' => $this->configuration[self::CONFIG_KINESIS_STREAM]
                ]);
            } catch (KinesisException $e) {
                throw new Exception(self::ERR_UNKNOWN, 0, $e);
            }

            if (!isset($results['Records']) || !is_array($results['Records'])) {
                throw new Exception(self::ERR_RESPONSE);
            }

            $messages = array_values(array_filter(array_map(function (array $message, array $result) {
                // merge original message with result
                return array_merge($message, $result);
            }, $messages, $results['Records']), function (array $message) {
                // remove messages that were successfully sent
                return isset($message['SequenceNumber']) && isset($message['ShardId']) ? false : true;
            }));

            $attempts++;
        }

        if (count($messages) > 0) {
            $errors[] = sprintf(self::ERR_BATCH, count($messages), $total, $attempts, $this->formatErrors($messages));
        }

        if (count($errors) > 0) {
            $this->handleErrors($errors);
        }
    }

    /**
     * Handle errors
     *
     * @param array $messages
     *
     * @throws Exception
     *
     * @return void
     */
    private function handleErrors(array $messages)
    {
        if ($this->configuration[self::CONFIG_IS_SILENT]) {
            foreach ($messages as $message) {
                error_log($message);
            }
            return;
        }

        if (count($messages) > 1) {
            throw new Exception(sprintf(self::ERR_MULTIPLE, count($messages), implode("\n", $messages)));
        }

        throw new Exception($messages[0]);
    }

    /**
     * Format Kinesis error messages nicely
     *
     * @param array $messages
     *
     * @return string
     */
    private function formatErrors(array $messages)
    {
        return implode(', ', array_unique(array_map(function (array $message) {
            return sprintf('%s (%s)', $message['ErrorCode'], $message['ErrorMessage']);
        }, $messages)));
    }
}
