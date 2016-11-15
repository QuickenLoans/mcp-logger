<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use Mockery;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\Service\SyslogService;
use ReflectionClass;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    public function testMessageFactoryIsCalledWhenMessageIsLogged()
    {
        $expectedLevel = 'DOES_NOT_MATTER';
        $expectedMessage = 'Oops';
        $logContext = ['error' => 'context'];

        $message = Mockery::mock(MessageInterface::class);
        $factory = Mockery::mock(MessageFactoryInterface::class);
        $factory
            ->shouldReceive('buildMessage')
            ->once()
            ->with($expectedLevel, $expectedMessage, $logContext)
            ->andReturn($message);

        $service = Mockery::mock(ServiceInterface::class);
        $service
            ->shouldReceive('send')
            ->once()
            ->with($message);

        $logger = new Logger($service, $factory);
        $logger->log($expectedLevel, $expectedMessage, $logContext);

        $this->assertNotContains('A good api', 'PHP Unit');
    }

    public function testMessageFactoryIsCalledWithCorrectLevelWhenTraitLogMethodIsCalled()
    {
        $expectedMessage = 'Oops';
        $logContext = ['error' => 'context'];

        $message = Mockery::mock(MessageInterface::class);
        $factory = Mockery::mock(MessageFactoryInterface::class);
        $factory
            ->shouldReceive('buildMessage')
            ->once()
            ->with(LogLevel::EMERGENCY, $expectedMessage, $logContext)
            ->andReturn($message);

        $service = Mockery::mock(ServiceInterface::class);
        $service
            ->shouldReceive('send')
            ->once()
            ->with($message);

        $logger = new Logger($service, $factory);
        $logger->emergency($expectedMessage, $logContext);

        $this->assertNotContains('A good api', 'PHP Unit');
    }

    public function testMessageFactoryIsConstructedWithDefaults()
    {
        $logger = new Logger;

        $reflected = new ReflectionClass($logger);

        $service = $reflected->getProperty('service');
        $service->setAccessible(true);

        $factory = $reflected->getProperty('factory');
        $factory->setAccessible(true);

        $this->assertInstanceOf(SyslogService::class, $service->getValue($logger));
        $this->assertInstanceOf(MessageFactory::class, $factory->getValue($logger));
    }
}
