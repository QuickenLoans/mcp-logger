<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Testing;

use MCP\DataType\IPv4Address;
use MCP\DataType\Time\Clock;
use MCP\Service\Logger\Renderer\XmlRenderer;
use PHPUnit_Framework_TestCase;
use QL\UriTemplate\UriTemplate;
use XMLWriter;

/**
 * @codeCoverageIgnore
 */
class IntegrationTestCase extends PHPUnit_Framework_TestCase
{
    public $uri;
    public $clock;
    public $renderer;

    public $defaultMessage;

    public function setUp()
    {
        $this->uri = new UriTemplate('http://qlsonictest:2581/web/core/logentries');
        $this->clock = new Clock('now', 'America/Detroit');
        $this->renderer = new XmlRenderer(new XMLWriter);

        $this->defaultMessage = [
            'applicationId' => '200001',
            'createTime' => $this->clock->read(),
            'machineIPAddress' => new IPv4Address(0),
            'machineName' => 'Test',
            'message' => 'Hello World!' // not actually required!
        ];
    }
}
