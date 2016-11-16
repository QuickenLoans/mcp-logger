<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger;

use HttpRequest;
use MCP\DataType\IPv4Address;
use MCP\DataType\Time\Clock;
use MCP\Service\Logger\Message\Message;
use MCP\Service\Logger\Renderer\XmlRenderer;
use MCP\Service\Logger\Service\HttpService;
use PHPUnit_Framework_TestCase;
use XMLWriter;

/**
 * @coversNothing
 * @group integration
 */
class IntegrationTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $request = new HttpRequest(
            'http://qlsonictest:2581/web/core/logentries',
            null,
            array(
                'timeout' => 5,
                'connecttimeout' => 5,
                'useragent' => 'MCP-TEST'
            )
        );

        $clock = new Clock('now', 'America/Detroit');
        $ip = new IPv4Address(0);

        $message = new Message(
            array(
                'applicationId' => '200001',
                'createTime' => $clock->read(),
                'machineIPAddress' => $ip,
                'machineName' => 'Test',
                'message' => 'Hello World!' // not actually required!
            )
        );

        $renderer = new XmlRenderer(new XMLWriter);
        $service = new HttpService($request, $renderer);

        $this->assertNull($service->send($message));
    }
}
