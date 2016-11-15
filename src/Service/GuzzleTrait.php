<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service;

use Exception as BaseException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface as Guzzle5ResponseInterface;
use Psr\Http\Message\ResponseInterface;
use QL\MCP\Logger\Exception;
use RuntimeException;

/**
 * Helper to allow GuzzleService to support both Guzzle 5 and Guzzle 6 APIs.
 */
trait GuzzleTrait
{
    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var int
     */
    private $version;

    /**
     * @param ClientInterface $guzzle
     *
     * @return void
     */
    private function setGuzzleClient(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * @throws Exception
     *
     * @return int
     */
    private function validateVersion()
    {
        $version = defined('GuzzleHttp\ClientInterface::VERSION') ? (int) substr(ClientInterface::VERSION, 0, 1) : 0;
        if ($version === 5 || $version === 6) {
            $this->version = $version;
            return $this->version;
        }

        throw new Exception('Guzzle 5 or Guzzle 6 are required to use this service.');
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return Guzzle5ResponseInterface|ResponseInterface|BaseException
     */
    private function requestGuzzle($method, $uri, array $options)
    {
        if ($this->version < 6) {
            return $this->requestGuzzle5($method, $uri, $options);
        }

        return $this->requestGuzzle6($method, $uri, $options);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return Guzzle5ResponseInterface|BaseException
     */
    private function requestGuzzle5($method, $uri, array $options)
    {
        $options = $options + [
            'exceptions' => true
        ];

        $request = $this->guzzle->createRequest($method, $uri, $options);

        try {
            $response = $this->guzzle->send($request);
        } catch(RuntimeException $e) {
            return $e;
        }

        return $response;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return ResponseInterface|BaseException
     */
    private function requestGuzzle6($method, $uri, array $options)
    {
        $options = $options + [
            'http_errors' => true
        ];

        try {
            $response = $this->guzzle->request($method, $uri, $options);
        } catch(RuntimeException $e) {
            return $e;
        }

        return $response;
    }
}
