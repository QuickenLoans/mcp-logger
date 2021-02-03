<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\Serializer\LineSerializer;
use QL\MCP\Logger\Service\ErrorlogService;
use QL\MCP\Logger\Transformer\QLLogSeverityTransformer;
use ReflectionClass;

class LoggerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public $message;
    public $factory;
    public $serializer;
    public $service;

    public function setUp(): void
    {
        $this->message = Mockery::mock(MessageInterface::class);

        $this->factory = Mockery::mock(MessageFactoryInterface::class);
        $this->serializer = Mockery::mock(SerializerInterface::class);
        $this->service = Mockery::mock(ServiceInterface::class);
    }

    public function testMessageFactoryIsCalledWhenMessageIsLogged()
    {
        $this->factory
            ->shouldReceive('buildMessage')
            ->with('info', 'Oops', ['error' => 'context'])
            ->andReturn($this->message)
            ->once();

        $this->serializer
            ->shouldReceive('__invoke')
            ->with($this->message)
            ->andReturn('formatted message')
            ->once();

        $this->service
            ->shouldReceive('send')
            ->with('info', 'formatted message')
            ->once();

        $logger = new Logger($this->service, $this->serializer, $this->factory);
        $logger->log('info', 'Oops', ['error' => 'context']);
    }

    public function testMessageFactoryIsCalledWithCorrectLevelWhenTransformerIsUsed()
    {
        $this->message
            ->shouldReceive('all')
            ->andReturn([]);
        $this->message
            ->shouldReceive('severity')
            ->andReturn('emergency');
        $this->message
            ->shouldReceive('message')
            ->andReturn('Oops');

        $this->factory
            ->shouldReceive('buildMessage')
            ->once()
            ->with(LogLevel::EMERGENCY, 'Oops', ['error' => 'context'])
            ->andReturn($this->message);

        $message = null;
        $this->serializer
            ->shouldReceive('__invoke')
            ->with(Mockery::on(function($v) use (&$message) {
                $message = $v;
                return true;
            }))
            ->andReturn('formatted message')
            ->once();

        $this->service
            ->shouldReceive('send')
            ->once()
            ->with('emergency', 'formatted message');

        $logger = new Logger($this->service, $this->serializer, $this->factory);
        $logger->addTransformer(new QLLogSeverityTransformer);

        $logger->emergency('Oops', ['error' => 'context']);

        // The severity was changed from emergency to fatal
        $this->assertSame('fatal', $message->severity());

    }

    public function testMessageFactoryIsConstructedWithDefaults()
    {
        $logger = new Logger;

        $reflected = new ReflectionClass($logger);

        $service = $reflected->getProperty('service');
        $service->setAccessible(true);

        $serializer = $reflected->getProperty('serializer');
        $serializer->setAccessible(true);

        $factory = $reflected->getProperty('factory');
        $factory->setAccessible(true);

        $this->assertInstanceOf(ErrorlogService::class, $service->getValue($logger));
        $this->assertInstanceOf(LineSerializer::class, $serializer->getValue($logger));
        $this->assertInstanceOf(MessageFactory::class, $factory->getValue($logger));
    }
}
