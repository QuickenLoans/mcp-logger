<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use QL\MCP\Logger\Exception;
use QL\MCP\Logger\ServiceInterface;
use RuntimeException;

/**
 * HTTP endpoint service for Guzzle 6.
 */
class GuzzleService implements ServiceInterface
{
    const DEFAULT_TIMEOUT = 2;

    // Error Messages
    const ERR_INVALID_ENDPOINT = 'Invalid logger endpoint provided. Please provide a complete HTTP or HTTPS URL endpoint.';

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @param string $endpoint
     * @param ClientInterface|null $guzzle
     *
     * @throws Exception
     */
    public function __construct($endpoint, ClientInterface $guzzle = null)
    {
        $this->guzzle = $guzzle ?: $this->buildDefaultGuzzle();

        $this->endpoint = $endpoint;

        $this->validateEndpoint($endpoint);
    }

    /**
     * @param string $level
     * @param string $formatted
     *
     * @return bool
     */
    public function send(string $level, string $formatted): bool
    {
        $options = [
            'body' => $formatted,
            'http_errors' => true
        ];

        try {
            $response = $this->guzzle->request('POST', $this->endpoint, $options);
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
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
     * @return ClientInterface
     */
    protected function buildDefaultGuzzle()
    {
        return new Client([
            'timeout' => self::DEFAULT_TIMEOUT,
            'connect_timeout' => self::DEFAULT_TIMEOUT
        ]);
    }
}
