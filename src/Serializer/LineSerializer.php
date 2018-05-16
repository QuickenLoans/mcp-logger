<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Serializer;

use QL\MCP\Logger\MessageInterface;
use QL\MCP\Logger\SerializerInterface;
use QL\MCP\Logger\Serializer\Utility\SanitizerTrait;

/**
 * Serializer for formatting messages into a single line.
 *
 * This implements some basic functionality and ideas from MonoLog:
 * https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php
 */
class LineSerializer implements SerializerInterface
{
    use SanitizerTrait;

    // Config Keys
    const CONFIG_TOKEN = 'token';
    const CONFIG_TEMPLATE = 'template';

    // Config Defaults
    const DEFAULT_TOKEN = '{{ VAR }}';
    const DEFAULT_TEMPLATE = '[{{ created }}] {{ severity }} : {{ message }}';

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        $this->configuration = $configuration + [
            self::CONFIG_TEMPLATE => self::DEFAULT_TEMPLATE,
            self::CONFIG_TOKEN => self::DEFAULT_TOKEN,
        ];
    }

    /**
     * @param MessageInterface $message
     *
     * @return string
     */
    public function __invoke(MessageInterface $message): string
    {
        $template = $this->configuration[self::CONFIG_TEMPLATE];

        $context = [
            'id' => $this->sanitizeGUID($message->id()),
            'message' => $this->sanitizeString($message->message()),
            'severity' => $this->sanitizeString($message->severity()),
            'created' => $this->sanitizeTime($message->created()),

            'details' => $this->sanitizeString($message->details()),

            'app' => $this->sanitizeString($message->applicationID()),
            'env' => $this->sanitizeString($message->serverEnvironment()),

            'server.ip' => $this->sanitizeString($message->serverIP()),
            'server.host' => $this->sanitizeString($message->serverHostname()),

            'request.method' => $this->sanitizeString($message->requestMethod()),
            'request.url' => $this->sanitizeString($message->requestURL()),

            'user.agent' => $this->sanitizeString($message->userAgent()),
            'user.ip' => $this->sanitizeString($message->userIP()),
        ];

        foreach ($message->context() as $key => $value) {
            $key = preg_replace("/[^a-z0-9_]/","_", strtolower($key));
            $context['context.' . $key] = $this->sanitizeString($value);
        }

        return $this->formatTemplate($template, $context);
    }

    /**
     * @param string $template
     * @param array $vars
     *
     * @return string
     */
    protected function formatTemplate($template, array $vars)
    {
        $output = $template;
        $token = $this->buildToken();

        foreach ($vars as $key => $value) {
            $replacement = sprintf($token, $key);
            if (false !== strpos($output, $replacement)) {
                $output = str_replace($replacement, $this->sanitizeNewlines($value), $output);
            }
        }

        $output = $this->removeExtraTokens($token, $output);

        return $output;
    }

    /**
     * @param mixed $content
     *
     * @return string
     */
    protected function sanitizeNewlines($content)
    {
        $content = (string) $content;
        return str_replace(["\r\n", "\r", "\n"], ' ', trim($content));
    }

    /**
     * @return string
     */
    private function buildToken()
    {
        $token = $this->configuration[self::CONFIG_TOKEN];
        if (substr_count($token, 'VAR') !== 1) {
            $token = self::DEFAULT_TOKEN;
        }

        return str_replace('VAR', '%s', $token);
    }

    /**
     * @param string $token
     * @param string $output
     *
     * @return string
     */
    private function removeExtraTokens($token, $output)
    {
        [$prefix, $suffix] = explode('%s', $token);

        $prefix = preg_quote($prefix, '/');
        $suffix = preg_quote($suffix, '/');

        $output = preg_replace('/' . $prefix . '(?:context)\..+?' . $suffix . '/', '', $output);

        return $output;
    }
}
