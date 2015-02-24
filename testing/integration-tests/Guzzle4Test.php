<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger;

use GuzzleHttp\Client;
use MCP\Logger\Message\Message;
use MCP\Logger\Service\Guzzle4Service;
use MCP\Logger\Testing\IntegrationTestTrait;
use PHPUnit_Framework_TestCase;

/**
 * @coversNothing
 * @group integration
 */
class Guzzle4IntegrationTest extends PHPUnit_Framework_TestCase
{
    use IntegrationTestTrait;

    public function test()
    {
        $guzzle = new Client;
        $service = new Guzzle4Service($guzzle, $this->renderer, $this->uri);

        $this->defaultMessage['extendedProperties']['serviceType'] = get_class($service);
        $message = new Message($this->defaultMessage);
        $response = $service->send($message);

        $this->assertNull($response);
    }
}
