<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service\Serializer;

use MCP\Logger\LogLevelInterface as QLLogLevel;
use MCP\Logger\MessageInterface;
use MCP\Logger\Service\SerializerInterface;

/**
 * JSON serializer for log messages.
 */
class JSONSerializer implements SerializerInterface
{
    use LogLevelTrait;
    use SanitizerTrait;

    // Config Keys
    const CONFIG_BACKLOAD_LIMIT = 'backload_limit';
    const CONFIG_JSON_OPTIONS = 'json_options';

    // Config Defaults
    const DEFAULT_BACKLOAD_LIMIT = 10000;
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
        $this->config = array_merge([
            self::CONFIG_BACKLOAD_LIMIT => self::DEFAULT_BACKLOAD_LIMIT,
            self::CONFIG_JSON_OPTIONS => self::DEFAULT_JSON_OPTIONS
        ], $config);
    }

    /**
     * @param MessageInterface $message
     *
     * @return string
     */
    public function __invoke(MessageInterface $message)
    {
        $severity = $this->convertLogLevelFromPSRToQL($message->severity());
        $isDisrupted = in_array($severity, [QLLogLevel::ERROR, QLLogLevel::FATAL]);

        $context = $message->context();

        $data = [
            'ID' => $this->sanitizeGUID($message->id()),
            'AppID' => $this->sanitizeInteger($message->applicationID()),
            'Created' => $this->sanitizeTime($message->created()),

            'UserIsDisrupted' => $isDisrupted,
            'Level' => $this->sanitizeString($severity),
            'ServerIP' => $this->sanitizeIP($message->serverIP()),
            'ServerHostname' => $this->sanitizeString($message->serverHostname()),
            'Message' => $this->sanitizeString($message->message())
        ];

        $optionals = [
            'Properties' => $this->buildContext($context),
            'Environment' => $this->sanitizeString($message->serverEnvironment()),
            'Exception' => $this->sanitizeString($message->errorDetails()),

            'Method' => $this->sanitizeString($message->requestMethod()),
            'URL' => $this->sanitizeString($message->requestURL()),

            'UserAgent' => $this->sanitizeString($message->userAgent()),
            'UserIP' => $this->sanitizeIP($message->userIP()),
            'UserName' => $this->sanitizeString($message->userName()),
        ];

        foreach ($optionals as $element => $value) {
            if ($value) {
                $data[$element] = $value;
            }
        }

        if (isset($data['Properties'])) {
            $this->backloadLargeProperties($data['Properties']);
        }

        $this->backloadLargeProperties($data);

        return json_encode($data, $this->config[self::CONFIG_JSON_OPTIONS]);
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'application/json';
    }

    /**
     * @param array $data
     * @param mixed[] $properties
     *
     * @return void
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

    /**
     * Ensures that all string fields with long values are placed at the end of the document
     *
     * @param array $data
     *
     * @return void
     */
    private function backloadLargeProperties(array &$data)
    {
        $large = [];

        foreach ($data as $key => $value) {
            if (is_string($value) && strlen($value) > $this->config[self::CONFIG_BACKLOAD_LIMIT]) {
                $large[$key] = $value;
                unset($data[$key]);
            }
        }

        $data = $data + $large;
    }
}
