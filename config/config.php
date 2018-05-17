<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use QL\MCP\Logger\Logger;
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\SerializerInterface;
use QL\MCP\Logger\Serializer\JSONSerializer;
use QL\MCP\Logger\Serializer\LineSerializer;
use QL\MCP\Logger\Serializer\XMLSerializer;
use QL\MCP\Logger\ServiceInterface;
use QL\MCP\Logger\Service\ErrorLogService;
use QL\MCP\Logger\Service\GuzzleService;
use QL\MCP\Logger\Service\NullService;
use QL\MCP\Logger\Service\SyslogService;

/**
 * MCP logger provides one main service:
 * - @mcp_logger
 *
 * The 3 main configuration options:
 * - MCP_LOGGER_SERVICE:    (one of [error_log, syslog, guzzle, null])  default: error_log
 * - MCP_LOGGER_SERIALIZER: (one of [json, line, xml])                  default: line
 *
 * - mcp_logger.default_properties (an array of properties to attach to all logs) [not available through env vars]
 *
 * These options tell the main logger to use one of the following serializers and/or services:
 * - mcp_logger.service.error_log
 * - mcp_logger.service.sys_log           You can customize these options by simply providing
 * - mcp_logger.service.guzzle            your own services and setting the config options
 * - mcp_logger.service.null              to your custom service
 * - mcp_logger.serializer.json
 * - mcp_logger.serializer.line           Example: 'mcp_logger.serializer.my_custom.serializer'
 * - mcp_logger.serializer.xml
 *
 * Configuration options (all through environment variables - note you can set these statically in config as well)
 *
 * - MCP_LOGGER_SERVICE
 * - MCP_LOGGER_SERIALIZER
 *
 * - MCP_LOGGER_NEWLINES_ENABLED
 * - MCP_LOGGER_LINE_SERIALIZER_TEMPLATE
 * - MCP_LOGGER_LINE_SERIALIZER_NEWLINES_ENABLED
 * - MCP_LOGGER_ERRORLOG_TYPE
 * - MCP_LOGGER_ERRORLOG_FILE
 * - MCP_LOGGER_SYSLOG_IDENT
 * - MCP_LOGGER_SYSLOG_FACILITY
 * - MCP_LOGGER_SYSLOG_OPTIONS
 * - MCP_LOGGER_GUZZLE_ENDPOINT
 * - MCP_LOGGER_SERIALIZER
 *
 */
return function (ContainerConfigurator $container) {

    $p = $container->parameters();
    $s = $container->services();

    $p
        ->set('env(MCP_LOGGER_SERVICE)', 'error_log')
        ->set('env(MCP_LOGGER_SERIALIZER)', 'line')

        ->set('env(MCP_LOGGER_NEWLINES_ENABLED)', '') # SPLIT_ON_NEWLINES

        ->set('mcp_logger.default_properties', [])
        ->set('mcp_logger.factory.options', [
            'max_size_kb' => '%env(int:MCP_LOGGER_MAX_SIZE_KB)%'
        ])

        // factory
        ->set('env(MCP_LOGGER_MAX_SIZE_KB)', 100)

        // line serializer
        ->set('env(MCP_LOGGER_LINE_SERIALIZER_TEMPLATE)', '[{{ created }}] {{ severity }} : {{ message }}')
        ->set('env(MCP_LOGGER_LINE_SERIALIZER_NEWLINES_ENABLED)', '') # ALLOW_NEWLINES

        // error log service
        ->set('env(MCP_LOGGER_ERRORLOG_TYPE)', 'OPERATING_SYSTEM') # OPERATING_SYSTEM, SAPI, FILE
        ->set('env(MCP_LOGGER_ERRORLOG_FILE)', '')

        // syslog service
        ->set('env(MCP_LOGGER_SYSLOG_IDENT)', 'mcplogger')
        ->set('env(MCP_LOGGER_SYSLOG_FACILITY)', LOG_USER)
        ->set('env(MCP_LOGGER_SYSLOG_OPTIONS)', LOG_ODELAY | LOG_CONS)

        // guzzle service
        ->set('env(MCP_LOGGER_GUZZLE_ENDPOINT)', 'https://logs.example.com')

        // options
        ->set('mcp_logger.line_serializer.options', [
            'template' => '%env(MCP_LOGGER_LINE_SERIALIZER_TEMPLATE)%'
        ])

        ->set('mcp_logger.error_log.options', [
            'type' => '%env(MCP_LOGGER_ERRORLOG_TYPE)%',
            'file' => '%env(MCP_LOGGER_ERRORLOG_FILE)%'
        ])

        ->set('mcp_logger.syslog.options', [
            'ident' => '%env(MCP_LOGGER_SYSLOG_IDENT)%',
            'facility' => '%env(MCP_LOGGER_SYSLOG_FACILITY)%',
            'options' => '%env(MCP_LOGGER_SYSLOG_OPTIONS)%'
        ])
    ;

    $s
        ->defaults()
            ->private()

        ->set(Logger::class)
            ->public()
            ->arg('$service', ref('mcp_logger.service'))
            ->arg('$serializer', ref('mcp_logger.serializer'))
            ->arg('$factory', ref(MessageFactory::class))
            ->call('withFlag', ['%env(MCP_LOGGER_NEWLINES_ENABLED)%'])

        ->set(MessageFactory::class)
            ->public()
            ->arg('$defaults', '%mcp_logger.default_properties%')
            ->arg('$config', '%mcp_logger.factory.options%')

        ->set('mcp_logger.service')
            ->class(ServiceInterface::class)
            ->factory([ref('service_container'), 'get'])
            ->arg('$id', 'mcp_logger.service.%env(MCP_LOGGER_SERVICE)%')

        ->set('mcp_logger.serializer')
            ->class(SerializerInterface::class)
            ->factory([ref('service_container'), 'get'])
            ->arg('$id', 'mcp_logger.serializer.%env(MCP_LOGGER_SERIALIZER)%')

        // Service types
        ->set('mcp_logger.service.error_log')
            ->class(ErrorLogService::class)
            ->arg('$config', '%mcp_logger.error_log.options%')
            ->public()

        ->set('mcp_logger.service.syslog')
            ->class(SyslogService::class)
            ->arg('$config', '%mcp_logger.syslog.options%')
            ->public()

        ->set('mcp_logger.service.guzzle')
            ->class(GuzzleService::class)
            ->arg('$endpoint', '%env(MCP_LOGGER_GUZZLE_ENDPOINT)%')
            ->public()

        ->set('mcp_logger.service.null')
            ->class(NullService::class)
            ->public()

        // Serializer types
        ->set('mcp_logger.serializer.json')
            ->class(JSONSerializer::class)
            ->public()

        ->set('mcp_logger.serializer.line')
            ->class(LineSerializer::class)
            ->arg('$config', '%mcp_logger.line_serializer.options%')
            ->call('withFlag', ['%env(MCP_LOGGER_LINE_SERIALIZER_NEWLINES_ENABLED)%'])
            ->public()

        ->set('mcp_logger.serializer.xml')
            ->class(XMLSerializer::class)
            ->public()
    ;

    $s->alias('mcp_logger', Logger::class)->public();
};
