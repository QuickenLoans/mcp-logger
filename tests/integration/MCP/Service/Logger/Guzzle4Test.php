<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger;

use GuzzleHttp\Client;
use MCP\DataType\IPv4Address;
use MCP\DataType\Time\Clock;
use MCP\Service\Logger\Message\Message;
use MCP\Service\Logger\Renderer\XmlRenderer;
use MCP\Service\Logger\Service\Guzzle4Service;
use PHPUnit_Framework_TestCase;
use XMLWriter;

/**
 * @coversNothing
 * @group integration
 */
class Guzzle4IntegrationTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $client = new Client(['base_url' => 'http://qlsonictest:2581/web/core/logentries']);
        $clock = new Clock('now', 'America/Detroit');

        $message = new Message([
                'applicationId' => '200001',
                'createTime' => $clock->read(),
                'machineIPAddress' => new IPv4Address(0),
                'machineName' => 'Test',
                'message' => 'Hello World!' // not actually required!
        ]);

        $renderer = new XmlRenderer(new XMLWriter);
        $service = new Guzzle4Service($client, $renderer);

        $this->assertNull($service->send($message));
    }
}
