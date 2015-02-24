<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger;

use Guzzle\Http\Client;
use MCP\DataType\IPv4Address;
use MCP\DataType\Time\Clock;
use MCP\Service\Logger\Message\Message;
use MCP\Service\Logger\Renderer\XmlRenderer;
use MCP\Service\Logger\Service\Guzzle3Service;
use PHPUnit_Framework_TestCase;
use QL\UriTemplate\UriTemplate;
use XMLWriter;

/**
 * @coversNothing
 * @group integration
 */
class Guzzle3IntegrationTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $client = new Client;
        $clock = new Clock('now', 'America/Detroit');

        $message = new Message([
            'applicationId' => '200001',
            'createTime' => $clock->read(),
            'machineIPAddress' => new IPv4Address(0),
            'machineName' => 'Test',
            'message' => 'Hello World!' // not actually required!
        ]);

        $uri = new UriTemplate('http://qlsonictest:2581/web/core/logentries');
        $renderer = new XmlRenderer(new XMLWriter);
        $service = new Guzzle3Service($client, $renderer, $uri);

        $this->assertNull($service->send($message));
    }
}
