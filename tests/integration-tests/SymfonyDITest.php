<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use PHPUnit\Framework\TestCase;
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\Serializer\JSONSerializer;
use QL\MCP\Logger\Serializer\LineSerializer;
use QL\MCP\Logger\Serializer\XMLSerializer;
use QL\MCP\Logger\Service\ErrorLogService;
use QL\MCP\Logger\Service\GuzzleService;
use QL\MCP\Logger\Service\NullService;
use QL\MCP\Logger\Service\SyslogService;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Config\FileLocator;

class SymfonyDITest extends TestCase
{
    public function getContainer()
    {
        $configRoot = __DIR__ . '/../../config';

        $container = new ContainerBuilder;
        $builder = new PhpFileLoader($container, new FileLocator($configRoot));
        $builder->load('config.php');
        return $container;
    }

    public function testContainerCompiles()
    {
        $container = $this->getContainer();
        $container->compile();
    }

    public function testServicesCanBePulled()
    {
        $container = $this->getContainer();
        $container->compile(true);

        // core components
        $this->assertInstanceOf(Logger::class, $container->get('mcp_logger'));
        $this->assertInstanceOf(MessageFactory::class, $container->get(MessageFactory::class));

        // // default service
        // $this->assertInstanceOf(SyslogService::class, $container->get('mcp.logger.service'));

        // // services
        // $this->assertInstanceOf(SyslogService::class, $container->get('mcp.logger.service.syslog'));
        // $this->assertInstanceOf(GuzzleService::class, $container->get('mcp.logger.service.guzzle'));
        // $this->assertInstanceOf(ErrorLogService::class, $container->get('mcp.logger.service.errorlog'));
        // $this->assertInstanceOf(NullService::class, $container->get('mcp.logger.service.null'));

        // // serializers
        // $this->assertInstanceOf(LineSerializer::class, $container->get('mcp.logger.serializer.line'));
        // $this->assertInstanceOf(JSONSerializer::class, $container->get('mcp.logger.serializer.json'));
        // $this->assertInstanceOf(XMLSerializer::class, $container->get('mcp.logger.serializer.xml'));
    }

    /**
     * @dataProvider configurationProviderForService
     */
    public function testCustomService($env, $class)
    {
        $container = $this->getContainer();
        putenv("MCP_LOGGER_SERVICE=${env}");
        $container->compile(true);

        $logger = $container->get('mcp_logger');
        $this->assertInstanceOf(Logger::class, $logger);

        $reflected = new ReflectionClass($logger);
        $service = $reflected->getProperty('service');
        $service->setAccessible(true);

        $this->assertInstanceOf($class, $service->getValue($logger));
    }

    /**
     * @dataProvider configurationProviderForSerializer
     */
    public function testCustomSerializer($env, $class)
    {
        $container = $this->getContainer();
        putenv("MCP_LOGGER_SERIALIZER=${env}");
        $container->compile(true);

        $logger = $container->get('mcp_logger');
        $this->assertInstanceOf(Logger::class, $logger);

        $reflected = new ReflectionClass($logger);
        $serializer = $reflected->getProperty('serializer');
        $serializer->setAccessible(true);

        $this->assertInstanceOf($class, $serializer->getValue($logger));
    }

    public function configurationProviderForService()
    {
        return [
            ['error_log', ErrorLogService::class],
            ['guzzle', GuzzleService::class],
            ['null', NullService::class],
            ['syslog', SyslogService::class],
        ];
    }

    public function configurationProviderForSerializer()
    {
        return [
            ['json', JSONSerializer::class],
            ['line', LineSerializer::class],
            ['xml', XMLSerializer::class],
        ];
    }
}
