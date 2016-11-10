<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service\Serializer;

use XMLWriter;

class XMLGenerator
{
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
     * @param array $doc
     *
     * @return string
     */
    public function generate(array $doc)
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
     * @return null
     */
    private function buildElement(XMLWriter $xml, $name, $property)
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
            $value = (filter_var($value, FILTER_VALIDATE_BOOLEAN)) ? 'true' : 'false';
        }

        return $value;
    }
}
