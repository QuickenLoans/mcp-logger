<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service\Renderer;

use DateTime;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\TimePoint;
use MCP\Logger\MessageInterface;
use MCP\Logger\Service\RendererInterface;
use XMLWriter;

/**
 * @see https://confluence/display/CORE/Core+Logger
 *
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
     * @param XMLWriter|null $writer
     */
    public function __construct(XMLWriter $writer = null)
    {
        $this->writer = $writer ?: new XMLWriter;
    }

    /**
     * @param MessageInterface $message
     *
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
        $this->addNode($xml, 'LogEntryClientID', strtolower(substr($message->id()->asHumanReadable(), 1, -1)));
        $this->addNode($xml, 'ApplicationId', $this->sanitizeInteger($message->applicationId()));
        $this->addNode($xml, 'CreateTime', $this->sanitizeTime($message->createTime()));

        // Optional - MUST BE BEFORE EXTENDED PROPERTIES
        $this->addOptionalNode($xml, 'ExceptionData', $this->sanitizeString($message->exceptionData()));

        $this->addExtendedPropertiesNode($xml, $message->extendedProperties());

        $this->addNode($xml, 'IsUserDisrupted', $this->sanitizeBoolean($message->isUserDisrupted()));
        $this->addNode($xml, 'Level', $this->sanitizeString($message->level()));

        $this->addNode($xml, 'MachineIPAddress', $this->sanitizeIP($message->machineIPAddress()));
        $this->addNode($xml, 'MachineName', $this->sanitizeString($message->machineName()));
        $this->addNode($xml, 'Message', $this->sanitizeString($message->message()));

        // Optional
        $this->addOptionalNode($xml, 'AffectedSystem', $this->sanitizeString($message->affectedSystem()));
        $this->addOptionalNode($xml, 'CategoryId', $this->sanitizeInteger($message->categoryId()));
        $this->addOptionalNode($xml, 'Referrer', $this->sanitizeString($message->referrer()));
        $this->addOptionalNode($xml, 'RequestMethod', $this->sanitizeString($message->requestMethod()));
        $this->addOptionalNode($xml, 'Url', $this->sanitizeString($message->url()));
        $this->addOptionalNode($xml, 'UserAgentBrowser', $this->sanitizeString($message->userAgentBrowser()));

        $this->addOptionalNode($xml, 'UserCommonId', $this->sanitizeInteger($message->userCommonId()));
        $this->addOptionalNode($xml, 'UserDisplayName', $this->sanitizeString($message->userDisplayName()));
        $this->addOptionalNode($xml, 'UserIPAddress', $this->sanitizeIP($message->userIPAddress()));
        $this->addOptionalNode($xml, 'UserName', $this->sanitizeString($message->userName()));
        $this->addOptionalNode($xml, 'UserScreenName', $this->sanitizeString($message->userScreenName()));

        $xml->endElement();

        return $xml->outputMemory(true);
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'text/xml';
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
     *
     * @return string
     */
    protected function sanitizeBoolean($value)
    {
        return (filter_var($value, FILTER_VALIDATE_BOOLEAN)) ? 'true' : 'false';
    }

    /**
     * @param int|string $value
     *
     * @return string
     */
    protected function sanitizeInteger($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * @param IPv4Address|null $value
     *
     * @return string|null
     */
    protected function sanitizeIP($value)
    {
        if ($value instanceof IPv4Address) {
            return $value->asString();
        }

        return null;
    }

    /**
     * @param int|string $value
     *
     * @return string
     */
    protected function sanitizeString($value)
    {
        return filter_var((string) $value, FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH);
    }

    /**
     * @param TimePoint|null $value
     *
     * @return string|null
     */
    protected function sanitizeTime($value)
    {
        if ($value instanceof TimePoint) {
            return $value->format('Y-m-d\TH:i:s\.u\Z', 'UTC');
        }

        return null;
    }
}
