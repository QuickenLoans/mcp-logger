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
use XMLWriter;

/**
 * XML serializer for log messages.
 */
class XMLSerializer implements SerializerInterface
{
    use SanitizerTrait;

    /**
     * @var XMLWriter
     */
    private $xml;

    /**
     * @param XMLWriter|null $writer
     */
    public function __construct(XMLWriter $writer = null)
    {
        $this->xml = $writer ?: new XMLWriter;
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
            'Details' => $this->sanitizeString($message->details())
        ];

        $optionals = [
            'AppID' => $this->sanitizeString($message->applicationID()),
            'Environment' => $this->sanitizeString($message->serverEnvironment()),

            'ServerIP' => $this->sanitizeString($message->serverIP()),
            'ServerHostname' => $this->sanitizeString($message->serverHostname()),

            'Method' => $this->sanitizeString($message->requestMethod()),
            'URL' => $this->sanitizeString($message->requestURL()),

            'UserAgent' => $this->sanitizeString($message->userAgent()),
            'UserIP' => $this->sanitizeString($message->userIP())
        ];

        foreach ($optionals as $element => $value) {
            if (strlen($value) > 0) {
                $data[$element] = $value;
            }
        }

        return $this->generate(['Entry' => $data]);
    }

    /**
     * @param array $properties
     *
     * @return array
     */
    protected function buildContext(array $properties)
    {
        $items = [];

        foreach ($properties as $key => $value) {
            $items[] = [
                    'Key' => $this->sanitizeString($key),
                    'Value' => $this->sanitizeString($value),
            ];
        }

        if (!$items) {
            return [];
        }

        return ['Entry' => $items];
    }


    /**
     * @param array $doc
     *
     * @return string
     */
    private function generate(array $doc)
    {
        $this->xml->openMemory();
        $this->xml->setIndentString(str_repeat(' ', 4));
        $this->xml->setIndent(true);

        $this->xml->startDocument('1.0', 'UTF-8');

        foreach ($doc as $name => $element) {
            $this->buildElement($this->xml, $name, $element);
        }

        $this->xml->endElement();
        return $this->xml->outputMemory();
    }

    /**
     * @param XMLWriter $xml
     * @param string $name
     * @param mixed $property
     *
     * @return void
     */
    private function buildElement(XMLWriter $xml, $name, $property): void
    {
        if (stripos($name, '@') === 0) {
            $property = $this->boolify($property);
            $xml->writeAttribute(substr($name, 1), $property);
            return;
        }

        if ($name === '#text') {
            $property = $this->boolify($property);
            $xml->text($property);
            return;
        }

        // Handle children of same name
        if (is_array($property) && isset($property[0])) {
            foreach ($property as $prop) {
                $this->buildElement($xml, $name, $prop);
            }
            return;
        }

        $xml->startElement($name);

        if (!is_array($property)) {
            $property = $this->boolify($property);
            $xml->text($property);
        } else {
            foreach ($property as $name => $prop) {
                $this->buildElement($xml, $name, $prop);
            }
        }

        $xml->endElement();
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function boolify($value)
    {
        if (is_bool($value)) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        return $value;
    }
}
