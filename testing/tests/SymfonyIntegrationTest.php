<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\Service\GuzzleService;
use QL\MCP\Logger\Service\ErrorLogService;
use QL\MCP\Logger\Service\NullService;
use QL\MCP\Logger\Service\SyslogService;
use QL\MCP\Logger\Service\Serializer\JSONSerializer;
use QL\MCP\Logger\Service\Serializer\LineSerializer;
use QL\MCP\Logger\Service\Serializer\XMLSerializer;

class SymfonyIntegrationTest extends PHPUnit_Framework_TestCase
{
    public function testContainerCompiles()
    {
        $configRoot = __DIR__ . '/../../configuration';

        $container = new ContainerBuilder;
        $builder = new YamlFileLoader($container, new FileLocator($configRoot));
        $builder->load('mcp-logger.yml');

        $container->compile();
    }

    public function testServicesCanBePulled()
    {
        $configRoot = __DIR__ . '/../../configuration';

        $container = new ContainerBuilder;
        $builder = new YamlFileLoader($container, new FileLocator($configRoot));
        $builder->load('mcp-logger.yml');

        $container->compile();

        // core components
        $this->assertInstanceOf(Logger::class, $container->get('mcp.logger'));
        $this->assertInstanceOf(MessageFactory::class, $container->get('mcp.logger.factory'));

        // default service
        $this->assertInstanceOf(SyslogService::class, $container->get('mcp.logger.service'));

        // services
        $this->assertInstanceOf(SyslogService::class, $container->get('mcp.logger.service.syslog'));
        $this->assertInstanceOf(GuzzleService::class, $container->get('mcp.logger.service.guzzle'));
        $this->assertInstanceOf(ErrorLogService::class, $container->get('mcp.logger.service.errorlog'));
        $this->assertInstanceOf(NullService::class, $container->get('mcp.logger.service.null'));

        // serializers
        $this->assertInstanceOf(LineSerializer::class, $container->get('mcp.logger.serializer.line'));
        $this->assertInstanceOf(JSONSerializer::class, $container->get('mcp.logger.serializer.json'));
        $this->assertInstanceOf(XMLSerializer::class, $container->get('mcp.logger.serializer.xml'));

    }
}
