<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Renderer;

use DateTime;
use MCP\Service\Logger\MessageInterface;
use MCP\Service\Logger\RendererInterface;
use XMLWriter;

/**
 * @internal
 */
class XmlRenderer implements RendererInterface
{
    /**#@+
     * @var string
     */
    const XMLNS_SCHEMA = 'http://www.w3.org/2001/XMLSchema-instance';
    const XMLNS_CORELOG = 'http://rock/framework/logging';
    const XMLNS_DNETSER = 'http://schemas.datacontract.org/2004/07/Rock.Framework.Logging';
    /**#@-*/

    /**
     * @var XMLWriter
     */
    private $writer;

    /**
     * @param XMLWriter $writer
     */
    public function __construct(XMLWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param MessageInterface $message
     * @return string
     */
    public function __invoke(MessageInterface $message)
    {
        $xml = $this->writer;

        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('    ');

        $xml->startElement('LogEntry');

        $xml->startAttribute('xmlns:i');
        $xml->text(self::XMLNS_SCHEMA);
        $xml->endAttribute();

        $xml->startAttribute('xmlns');
        $xml->text(self::XMLNS_CORELOG);
        $xml->endAttribute();

        // Required
        $this->addNode($xml, 'ApplicationId', $this->sanitizeInteger($message->applicationId()));

        $date = ($message->createTime() !== null) ? $message->createTime()->format(DateTime::RFC3339, 'UTC') : null;
        $this->addNode($xml, 'CreateTime', $date);

        $this->addExtendedPropertiesNode($xml, $message->extendedProperties());

        $this->addNode($xml, 'IsUserDisrupted', $this->sanitizeBoolean($message->isUserDisrupted()));
        $this->addNode($xml, 'Level', $this->sanitizeString($message->level()));

        $ip = ($message->machineIPAddress() !== null) ? $message->machineIPAddress()->asString() : null;
        $this->addNode($xml, 'MachineIPAddress', $ip);

        $this->addNode($xml, 'MachineName', $this->sanitizeString($message->machineName()));
        $this->addNode($xml, 'Message', $this->sanitizeString($message->message()));

        // Optional
        $this->addOptionalNode($xml, 'AffectedSystem', $this->sanitizeString($message->affectedSystem()));
        $this->addOptionalNode($xml, 'CategoryId', $this->sanitizeInteger($message->categoryId()));
        $this->addOptionalNode($xml, 'ExceptionData', $this->sanitizeString($message->exceptionData()));
        $this->addOptionalNode($xml, 'Referrer', $this->sanitizeString($message->referrer()));
        $this->addOptionalNode($xml, 'RequestMethod', $this->sanitizeString($message->requestMethod()));
        $this->addOptionalNode($xml, 'Url', $this->sanitizeString($message->url()));
        $this->addOptionalNode($xml, 'UserAgentBrowser', $this->sanitizeString($message->userAgentBrowser()));
        $this->addOptionalNode($xml, 'UserCommonId', $this->sanitizeInteger($message->userCommonId()));
        $this->addOptionalNode($xml, 'UserDisplayName', $this->sanitizeString($message->userDisplayName()));

        $ip = ($message->userIPAddress() !== null) ? $message->userIPAddress()->asString() : null;
        $this->addOptionalNode($xml, 'UserIPAddress', $ip);

        $this->addOptionalNode($xml, 'UserName', $this->sanitizeString($message->userName()));
        $this->addOptionalNode($xml, 'UserScreenName', $this->sanitizeString($message->userScreenName()));

        $xml->endElement();

        return $xml->outputMemory(true);
    }

    /**
     * @param XMLWriter $xml
     * @param string $name
     * @param mixed $value
     * @return null
     */
    protected function addNode(XMLWriter $xml, $name, $value)
    {
        $xml->startElement($name);
        $xml->text($value);
        $xml->endElement();
    }

    /**
     * @param XMLWriter $xml
     * @param mixed[] $properties
     * @return null
     */
    protected function addExtendedPropertiesNode(XMLWriter $xml, $properties)
    {
        if (!is_array($properties)) {
            $properties = array();
        }

        $xml->startElement('ExtendedProperties');

        $xml->startAttribute('xmlns:d2p1');
        $xml->text(self::XMLNS_DNETSER);
        $xml->endAttribute();

        foreach ($properties as $key => $value) {
            $xml->startElement('d2p1:Entry');

                $xml->startElement('d2p1:Key');
                $xml->text($this->sanitizeString($key));
                $xml->endElement();

                $xml->startElement('d2p1:Value');
                $xml->text($this->sanitizeString($value));
                $xml->endElement();

            $xml->endElement();
        }

        $xml->endElement();
    }

    /**
     * @param XMLWriter $xml
     * @param string $name
     * @param mixed $value
     * @return null
     */
    protected function addOptionalNode(XMLWriter $xml, $name, $value)
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->addNode($xml, $name, $value);
    }

    /**
     * @param boolean $value
     * @return string
     */
    protected function sanitizeBoolean($value)
    {
        return (filter_var($value, FILTER_VALIDATE_BOOLEAN)) ? 'true' : 'false';
    }

    /**
     * @param int|string $value
     * @return string
     */
    protected function sanitizeInteger($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * @param int|string $value
     * @return string
     */
    protected function sanitizeString($value)
    {
        return filter_var((string) $value, FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH);
    }
}
