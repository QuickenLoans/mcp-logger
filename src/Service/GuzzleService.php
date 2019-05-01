<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use Exception as BaseException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface as Guzzle5ResponseInterface;
use QL\MCP\Logger\Exception;
use QL\MCP\Logger\MessageInterface;
use QL\MCP\Logger\ServiceInterface;
use QL\MCP\Logger\Service\Serializer\XMLSerializer;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP endpoint service for Guzzle 5 and 6.
 */
class GuzzleService implements ServiceInterface
{
    use GuzzleTrait;

    // Configuration Keys
    const CONFIG_SILENT = 'silent';
    const CONFIG_TIMEOUT = 'timeout';
    const CONFIG_CONNECT_TIMEOUT = 'connect_timeout';
    const CONFIG_ENDPOINT = 'endpoint';

    // Configuration Defaults
    const DEFAULT_SILENT = true;
    const DEFAULT_TIMEOUT = 2;
    const DEFAULT_CONNECT_TIMEOUT = 1;
    const DEFAULT_ENDPOINT = '';

    // Error Messages
    const ERR_INVALID_ENDPOINT = 'Invalid logger endpoint provided. Please provide a complete HTTP or HTTPS URL endpoint.';
    const ERR_BACKUP_TEMPLATE = 'MCP HTTP Logger failed : %s (%s)';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param string $endpoint
     * @param ClientInterface|null $guzzle
     * @param SerializerInterface|null $serializer
     * @param array $configuration
     *
     * @throws Exception
     */
    public function __construct(
        $endpoint = self::DEFAULT_ENDPOINT,
        ClientInterface $guzzle = null,
        SerializerInterface $serializer = null,
        array $configuration = []
    ) {
        $this->setGuzzleClient($guzzle ?: $this->buildDefaultGuzzle());
        $this->serializer = $serializer ?: $this->buildDefaultSerializer();

        $this->configuration = array_merge([
            self::CONFIG_SILENT => self::DEFAULT_SILENT,
            self::CONFIG_TIMEOUT => self::DEFAULT_TIMEOUT,
            self::CONFIG_CONNECT_TIMEOUT => self::DEFAULT_CONNECT_TIMEOUT,
            self::CONFIG_ENDPOINT => $endpoint
        ], $configuration);

        $this->validateEndpoint($this->configuration[self::CONFIG_ENDPOINT]);
        $this->validateVersion();
    }

    /**
     * @param MessageInterface $message
     *
     * @return void
     */
    public function send(MessageInterface $message)
    {
        $body = call_user_func($this->serializer, $message);
        $headers = ['Content-Type' => $this->serializer->contentType()];

        $response = $this->request($body, $headers);

        if (!$response instanceof BaseException) {
            return;
        }

        $error = $response->getMessage();
        $sanitized = strstr($message->message(), "\n", true) ?: $message->message();
        $msg = sprintf(self::ERR_BACKUP_TEMPLATE, $sanitized, $error);

        // quiet error
        if ($this->configuration[self::CONFIG_SILENT]) {
            error_log($msg, 0);
            return;
        }

        // noisy error
        throw new Exception($msg);
    }

    /**
     * @param string $endpoint
     *
     * @throws Exception
     *
     * @return void
     */
    private function validateEndpoint($endpoint)
    {
        $isValid = filter_var($endpoint, FILTER_VALIDATE_URL);

        if ($isValid) {
            $scheme = strtolower(parse_url($endpoint, PHP_URL_SCHEME));
            if (in_array($scheme, ['http', 'https'], true)) {
                return;
            }
        }

        throw new Exception(self::ERR_INVALID_ENDPOINT);
    }

    /**
     * @param string $body
     * @param array $headers
     *
     * @return Guzzle5ResponseInterface|ResponseInterface|BaseException
     */
    private function request($body, array $headers)
    {
        $method = 'POST';
        $uri = $this->configuration[self::CONFIG_ENDPOINT];
        $options = [
            'body' => $body,
            'headers' => $headers,
            'timeout' => $this->configuration[self::CONFIG_TIMEOUT],
            'connect_timeout' => $this->configuration[self::CONFIG_CONNECT_TIMEOUT]
        ];

        return $this->requestGuzzle($method, $uri, $options);
    }

    /**
     * @return ClientInterface
     */
    protected function buildDefaultGuzzle()
    {
        return new Client;
    }

    /**
     * @return SerializerInterface
     */
    protected function buildDefaultSerializer()
    {
        return new XMLSerializer;
    }
}
