<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Serializer;

use DateTime;
use QL\MCP\Logger\MessageInterface;

/**
 * Helper to simplify formatting log details into a single line.
 *
 * This implements some basic functionality and ideas from MonoLog:
 * https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php
 */
trait LineFormatterTrait
{
    /**
     * @param MessageInterface $message
     * @param string $template
     *
     * @return string
     */
    protected function formatMessage(MessageInterface $message, $template = '')
    {
        $template = $template ?: $this->defaultTemplate();

        $created = $message->created()->format(DateTime::ATOM, 'UTC');

        $context = [
            'id' => $message->id()->asHex(),
            'severity' => $message->severity(),
            'message' => $message->message(),
            'app' => $message->applicationID(),
            'created' => str_replace('+00:00', 'Z', $created),

            'server.ip' => $message->serverIP(),
            'server.host' => $message->serverHostname(),
            'server.env' => $message->serverEnvironment(),

            'method' => $message->requestMethod(),
            'url' => $message->requestURL(),
            'ip' => $message->userIP(),
            'user' => $message->userName(),
        ];

        foreach ($message->context() as $key => $value) {
            $context['context.' . strtolower($key)] = $value;
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

        foreach ($vars as $key => $value) {
            $token = '%' . $key . '%';
            if (false !== strpos($output, $token)) {
                $output = str_replace($token, $this->sanitize($value), $output);
            }
        }

        // Remove extra contexts
        if (false !== strpos($output, '%')) {
            $output = preg_replace('/%(?:context)\..+?%/', '', $output);
        }

        return $output;
    }

    /**
     * @return string
     */
    protected function defaultTemplate()
    {
        return '[%created%] %severity% : %message% (App ID: %app%, Server: %server.host%)';
    }

    /**
     * @param mixed $content
     *
     * @return string
     */
    protected function sanitize($content)
    {
        $content = (string) $content;
        return str_replace(["\r\n", "\r", "\n"], ' ', trim($content));
    }
}
