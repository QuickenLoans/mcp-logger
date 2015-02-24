<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger;

use Guzzle\Http\Client;
use MCP\Service\Logger\Message\Message;
use MCP\Service\Logger\Service\Guzzle3Service;
use MCP\Testing\IntegrationTestCase;

/**
 * @coversNothing
 * @group integration
 */
class Guzzle3IntegrationTest extends IntegrationTestCase
{
    public function test()
    {
        $guzzle = new Client;
        $service = new Guzzle3Service($guzzle, $this->renderer, $this->uri);

        $this->defaultMessage['extendedProperties']['serviceType'] = get_class($service);
        $message = new Message($this->defaultMessage);
        $response = $service->send($message);

        $this->assertNull($response);
    }
}
