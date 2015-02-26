<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger;

use GuzzleHttp\Client;
use MCP\Logger\Message\Message;
use MCP\Logger\Service\Guzzle5Service;
use MCP\Logger\Testing\IntegrationTestTrait;
use PHPUnit_Framework_TestCase;

/**
 * @coversNothing
 * @group integration
 */
class Guzzle5Test extends PHPUnit_Framework_TestCase
{
    use IntegrationTestTrait;

    public function test()
    {
        $guzzle = new Client;
        $service = new Guzzle5Service($guzzle, $this->renderer, $this->uri, false, false, 3);

        $this->defaultMessage['extendedProperties']['serviceType'] = get_class($service);

        $message1 = new Message(array_merge($this->defaultMessage, ['message' => 'GUZZLE5 ' . $this->defaultMessage['message']. ' : 1']));
        $message2 = new Message(array_merge($this->defaultMessage, ['message' => 'GUZZLE5 ' . $this->defaultMessage['message']. ' : 2']));
        $message3 = new Message(array_merge($this->defaultMessage, ['message' => 'GUZZLE5 ' . $this->defaultMessage['message']. ' : 3']));
        $message4 = new Message(array_merge($this->defaultMessage, ['message' => 'GUZZLE5 ' . $this->defaultMessage['message']. ' : 4']));

        $service->send($message1);
        $service->send($message2);
        $service->send($message3);

        // nothing sent at this point

        // now all messages sent together
        $response = $service->send($message4);

        $this->assertNull($response);
    }
}
