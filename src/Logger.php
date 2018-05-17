<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use QL\MCP\Logger\Message\MessageFactory;
use QL\MCP\Logger\Serializer\LineSerializer;
use QL\MCP\Logger\Service\ErrorLogService;
use QL\MCP\Logger\Utility\OptionTrait;

class Logger implements LoggerInterface
{
    use LoggerTrait;
    use OptionTrait;

    // Flags
    const SPLIT_ON_NEWLINES = 1;

    /**
     * @var ServiceInterface
     */
    private $service;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var MessageFactoryInterface
     */
    private $factory;

    /**
     * @var array
     */
    private $transformers;

    /**
     * @param ServiceInterface $service
     * @param MessageFactoryInterface $factory
     */
    public function __construct(
        ServiceInterface $service = null,
        SerializerInterface $serializer = null,
        MessageFactoryInterface $factory = null
    ) {
        $this->service = $service ?: $this->buildDefaultService();
        $this->serializer = $serializer ?: $this->buildDefaultSerializer();
        $this->factory = $factory ?: $this->buildDefaultFactory();

        $this->transformers = [];
    }

    /**
     * @param TransformerInterface $transformer
     *
     * @return void
     */
    public function addTransformer(TransformerInterface $transformer): void
    {
        $this->transformers[] = $transformer;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $message = $this->factory->buildMessage($level, $message, $context);

        foreach ($this->transformers as $transform) {
            $message = $transform($message);
        }

        $formatted = ($this->serializer)($message);

        if (!$this->isFlagEnabled(self::SPLIT_ON_NEWLINES)) {
            $this->service->send($level, $formatted);
            return;
        }

        $lines = preg_split('/(\r\n|\n|\r)/', $formatted);
        foreach ($lines as $line) {
            $this->service->send($level, $line);
        }
    }

    /**
     * @return ServiceInterface
     */
    protected function buildDefaultService()
    {
        return new ErrorLogService;
    }

    /**
     * @return SerializerInterface
     */
    protected function buildDefaultSerializer()
    {
        return new LineSerializer;
    }

    /**
     * @return MessageFactoryInterface
     */
    protected function buildDefaultFactory()
    {
        return new MessageFactory;
    }
}
