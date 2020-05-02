<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Serializer;

use QL\MCP\Logger\MessageInterface;
use QL\MCP\Logger\Serializer\Utility\SanitizerTrait;
use QL\MCP\Logger\SerializerInterface;

/**
 * JSON serializer for log messages.
 */
class JSONSerializer implements SerializerInterface
{
    use SanitizerTrait;

    // Config Keys
    const CONFIG_JSON_OPTIONS = 'json_options';

    // Config Defaults
    const DEFAULT_JSON_OPTIONS = JSON_UNESCAPED_SLASHES;

    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config + [
            self::CONFIG_JSON_OPTIONS => self::DEFAULT_JSON_OPTIONS,
        ];
    }

    /**
     * @param MessageInterface $message
     *
     * @return string
     */
    public function __invoke(MessageInterface $message): string
    {
        $data = [
            'ID' => $this->sanitizeGUID($message->id()),
            'Message' => $this->sanitizeString($message->message()),
            'Level' => $this->sanitizeString($message->severity()),
            'Created' => $this->sanitizeTime($message->created()),

            'Properties' => $this->buildContext($message->context()),
            'Details' => $this->sanitizeString($message->details()),
        ];

        $optionals = [
            'AppID' => $this->sanitizeString($message->applicationID()),
            'Environment' => $this->sanitizeString($message->serverEnvironment()),

            'ServerIP' => $this->sanitizeString($message->serverIP()),
            'ServerHostname' => $this->sanitizeString($message->serverHostname()),

            'Method' => $this->sanitizeString($message->requestMethod()),
            'URL' => $this->sanitizeString($message->requestURL()),

            'UserAgent' => $this->sanitizeString($message->userAgent()),
            'UserIP' => $this->sanitizeString($message->userIP()),
        ];

        foreach ($optionals as $element => $value) {
            if (strlen($value) > 0) {
                $data[$element] = $value;
            }
        }

        return json_encode($data, $this->config[self::CONFIG_JSON_OPTIONS]);
    }

    /**
     * @param mixed[] $properties
     *
     * @return array
     */
    protected function buildContext($properties)
    {
        if (!is_array($properties) || !$properties) {
            return [];
        }

        $extended = [];
        foreach ($properties as $k => $prop) {
            $extended[$k] = $this->sanitizeString($prop);
        }

        return $extended;
    }
}
