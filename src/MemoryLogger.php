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

class MemoryLogger implements LoggerInterface
{
    use LoggerTrait;

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
     * @var array
     */
    private $messages;

    /**
     * @param SerializerInterface $serializer
     * @param MessageFactoryInterface $factory
     */
    public function __construct(
        SerializerInterface $serializer = null,
        MessageFactoryInterface $factory = null
    ) {
        $this->serializer = $serializer ?: $this->buildDefaultSerializer();
        $this->factory = $factory ?: $this->buildDefaultFactory();

        $this->transformers = [];
        $this->messages = [];
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
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
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

        $this->messages[] = $formatted;
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
