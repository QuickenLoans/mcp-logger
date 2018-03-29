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
 * XML serializer for log messages.
 */
class XMLSerializer implements SerializerInterface
{
    use LogLevelTrait;
    use SanitizerTrait;

    /**
     * @var string
     */
    const XMLNS_SCHEMA = 'http://www.w3.org/2001/XMLSchema-instance';
    const XMLNS_CORELOG = 'http://rock/framework/logging';

    /**
     * @var XMLGenerator
     */
    private $generator;

    /**
     * @param XMLGenerator|null $generator
     */
    public function __construct(XMLGenerator $generator = null)
    {
        $this->generator = $generator ?: new XMLGenerator;
    }

    /**
     * @param MessageInterface $message
     *
     * @return string
     */
    public function __invoke(MessageInterface $message)
    {
        $severity = $this->convertLogLevelFromPSRToQL($message->severity());

        $context = $message->context();
        $context['LogEntryClientID'] = $this->sanitizeGUID($message->id());

        $entry = [
            '@xmlns:i' => self::XMLNS_SCHEMA,
            '@xmlns' => self::XMLNS_CORELOG,
            'ApplicationId' => $this->sanitizeInteger($message->applicationID()),
            'CreateTime' => $this->sanitizeTime($message->created()),
            'ExtendedProperties' => $this->buildContext($context),
            'IsUserDisrupted' => $this->isLogLevelDisruptive($message->severity()),
            'Level' => $this->sanitizeString($severity),
            'MachineIPAddress' => $this->sanitizeIP($message->serverIP()),
            'MachineName' => $this->sanitizeString($message->serverHostname()),
            'Message' => $this->sanitizeString($message->message())
        ];

        $optionals = [
            'Environment' => $this->sanitizeString($message->serverEnvironment()),
            'ExceptionData' => $this->sanitizeString($message->errorDetails()),

            'RequestMethod' => $this->sanitizeString($message->requestMethod()),
            'Url' => $this->sanitizeString($message->requestURL()),

            'UserAgentBrowser' => $this->sanitizeString($message->userAgent()),
            'UserIPAddress' => $this->sanitizeIP($message->userIP()),
            'UserName' => $this->sanitizeString($message->userName()),
        ];

        foreach ($optionals as $element => $value) {
            if ($value) {
                $entry[$element] = $value;
            }
        }

        return $this->generator->generate(['LogEntry' => $entry]);
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'text/xml';
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
                'd2p1:Key' => $this->sanitizeString($key),
                'd2p1:Value' => $this->sanitizeString($value),
            ];
        }

        return [
            'd2p1:Entry' => $items
        ];
    }
}
