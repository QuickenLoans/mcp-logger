<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger;

use HttpRequest;
use MCP\Service\Logger\Message\Message;
use MCP\Service\Logger\Service\PeclHttpService;
use MCP\Testing\IntegrationTestCase;

/**
 * @coversNothing
 * @group integration
 */
class PeclIntegrationTest extends IntegrationTestCase
{
    public function test()
    {
        $request = new HttpRequest;
        $request->setOptions([
            'timeout' => 5,
            'connecttimeout' => 5,
            'useragent' => 'MCP-TEST'
        ]);

        $service = new PeclHttpService($request, $this->renderer, $this->uri);

        $this->defaultMessage['extendedProperties']['serviceType'] = get_class($service);
        $message = new Message($this->defaultMessage);
        $response = $service->send($message);

        $this->assertNull($response);
    }
}
