<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service\Serializer;

use QL\MCP\Logger\MessageInterface;
use QL\MCP\Logger\Service\SerializerInterface;

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
    const CONFIG_TEMPLATE = 'template';

    // Config Defaults
    const DEFAULT_TEMPLATE = '[%created%] %severity% : %message% (App ID: %app%, Server: %server.host%)';

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        $this->configuration = array_merge([
            self::CONFIG_TEMPLATE => self::DEFAULT_TEMPLATE
        ], $configuration);
    }

    /**
     * @param MessageInterface $message
     *
     * @return string
     */
    public function __invoke(MessageInterface $message)
    {
        $template = $this->configuration[self::CONFIG_TEMPLATE];

        $context = [
            'id' => $this->sanitizeGUID($message->id()),
            'severity' => $message->severity(),
            'message' => $message->message(),
            'app' => $message->applicationID(),
            'created' => $this->sanitizeTime($message->created()),

            'server.ip' => $this->sanitizeIP($message->serverIP()),
            'server.host' => $message->serverHostname(),
            'server.env' => $message->serverEnvironment(),

            'method' => $message->requestMethod(),
            'url' => $message->requestURL(),
            'ip' => $this->sanitizeIP($message->userIP()),
            'user' => $message->userName(),
        ];

        foreach ($message->context() as $key => $value) {
            $context['context.' . strtolower($key)] = $value;
        }

        return $this->formatTemplate($template, $context);
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'text/plain';
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

        foreach ($vars as $key => $value) {
            $token = '%' . $key . '%';
            if (false !== strpos($output, $token)) {
                $output = str_replace($token, $this->sanitizeNewlines($value), $output);
            }
        }

        // Remove extra contexts
        if (false !== strpos($output, '%')) {
            $output = preg_replace('/%(?:context)\..+?%/', '', $output);
        }

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
}
