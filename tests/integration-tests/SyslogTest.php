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
use QL\MCP\Logger\Serializer\LineSerializer;
use QL\MCP\Logger\Service\SyslogService;

class StructuredLoggerTest extends TestCase
{
    public function test()
    {
        $serializer = new LineSerializer;
        $service = new SyslogService([
            'ident' => 'myapplication',
            'facility' => LOG_USER,
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
    }
}

