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
class Guzzle4Test extends PHPUnit_Framework_TestCase
{
    use IntegrationTestTrait;

    public function test()
    {
        $guzzle = new Client;
        $service = new Guzzle4Service($guzzle, $this->renderer, $this->uri);

        $message = new Message(array_merge($this->defaultMessage, ['message' => 'GUZZLE4 ' . $this->defaultMessage['message']]));
        $response = $service->send($message);

        $this->assertNull($response);
    }
}
