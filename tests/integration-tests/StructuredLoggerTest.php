<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use PHPUnit\Framework\TestCase;
use QL\MCP\Common\GUID;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\Clock;
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\Serializer\JSONSerializer;
use QL\MCP\Logger\Service\ErrorLogService;

class StructuredLoggerTest extends TestCase
{
    public function tearDown()
    {
        @unlink(__DIR__ . '/errlog');
    }

    public function test()
    {
        $serializer = new JSONSerializer;
        $service = new ErrorLogService([
            'type' => 'FILE',
            'file' => __DIR__ . '/errlog',
        ]);

        $factory = new MessageFactory;
        $factory->setDefaultProperty('applicationID', '200001');
        $factory->setDefaultProperty('serverEnvironment', 'prod');
        $factory->setDefaultProperty('serverIP', IPv4Address::create('127.0.0.1'));
        $factory->setDefaultProperty('serverHostname', 'prod.example.com');
        $factory->setDefaultProperty('created', (new Clock('2019-02-05 12:15:45', 'UTC'))->read());

        $logger = new Logger($service, $serializer, $factory);

        $context = [
            'testvar' => 1234,
        ];

        $logger->emergency('test message - alfa',   $context + ['id' => GUID::createFromHex('1111E37AD36C4F88B142A417670C93CF')]);
        $logger->alert('test message - bravo',      $context + ['id' => GUID::createFromHex('2222E37AD36C4F88B142A417670C93CF')]);
        $logger->critical('test message - charlie', $context + ['id' => GUID::createFromHex('3333E37AD36C4F88B142A417670C93CF')]);
        $logger->error('test message - delta',      $context + ['id' => GUID::createFromHex('4444E37AD36C4F88B142A417670C93CF')]);
        $logger->warning('test message - echo',     $context + ['id' => GUID::createFromHex('5555E37AD36C4F88B142A417670C93CF')]);
        $logger->notice('test message - foxtrot',   $context + ['id' => GUID::createFromHex('6666E37AD36C4F88B142A417670C93CF')]);
        $logger->info('test message - golf',        $context + ['id' => GUID::createFromHex('7777E37AD36C4F88B142A417670C93CF')]);
        $logger->debug('test message - hotel',      $context + ['id' => GUID::createFromHex('8888E37AD36C4F88B142A417670C93CF')]);

        $actual = file_get_contents(__DIR__ . '/errlog');

        $expectedLines = <<<EXPECTED_TEXT
{"ID":"1111e37a-d36c-4f88-b142-a417670c93cf","Message":"test message - alfa","Level":"emergency","Created":"2019-02-05T12:15:45.000000Z","Properties":{"testvar":"1234"},"Details":"","AppID":"200001","Environment":"prod","ServerIP":"127.0.0.1","ServerHostname":"prod.example.com"}
{"ID":"2222e37a-d36c-4f88-b142-a417670c93cf","Message":"test message - bravo","Level":"alert","Created":"2019-02-05T12:15:45.000000Z","Properties":{"testvar":"1234"},"Details":"","AppID":"200001","Environment":"prod","ServerIP":"127.0.0.1","ServerHostname":"prod.example.com"}
{"ID":"3333e37a-d36c-4f88-b142-a417670c93cf","Message":"test message - charlie","Level":"critical","Created":"2019-02-05T12:15:45.000000Z","Properties":{"testvar":"1234"},"Details":"","AppID":"200001","Environment":"prod","ServerIP":"127.0.0.1","ServerHostname":"prod.example.com"}
{"ID":"4444e37a-d36c-4f88-b142-a417670c93cf","Message":"test message - delta","Level":"error","Created":"2019-02-05T12:15:45.000000Z","Properties":{"testvar":"1234"},"Details":"","AppID":"200001","Environment":"prod","ServerIP":"127.0.0.1","ServerHostname":"prod.example.com"}
{"ID":"5555e37a-d36c-4f88-b142-a417670c93cf","Message":"test message - echo","Level":"warning","Created":"2019-02-05T12:15:45.000000Z","Properties":{"testvar":"1234"},"Details":"","AppID":"200001","Environment":"prod","ServerIP":"127.0.0.1","ServerHostname":"prod.example.com"}
{"ID":"6666e37a-d36c-4f88-b142-a417670c93cf","Message":"test message - foxtrot","Level":"notice","Created":"2019-02-05T12:15:45.000000Z","Properties":{"testvar":"1234"},"Details":"","AppID":"200001","Environment":"prod","ServerIP":"127.0.0.1","ServerHostname":"prod.example.com"}
{"ID":"7777e37a-d36c-4f88-b142-a417670c93cf","Message":"test message - golf","Level":"info","Created":"2019-02-05T12:15:45.000000Z","Properties":{"testvar":"1234"},"Details":"","AppID":"200001","Environment":"prod","ServerIP":"127.0.0.1","ServerHostname":"prod.example.com"}
{"ID":"8888e37a-d36c-4f88-b142-a417670c93cf","Message":"test message - hotel","Level":"debug","Created":"2019-02-05T12:15:45.000000Z","Properties":{"testvar":"1234"},"Details":"","AppID":"200001","Environment":"prod","ServerIP":"127.0.0.1","ServerHostname":"prod.example.com"}
EXPECTED_TEXT;

        foreach (explode("\n", $expectedLines) as $line) {
            $this->assertContains($line, $actual);
        }
    }
}
