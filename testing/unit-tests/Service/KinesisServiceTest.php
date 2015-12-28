<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use Aws\Kinesis\KinesisClient;
use MCP\Logger\Message\Message;
use MCP\Logger\Renderer\JsonRenderer;
use PHPUnit_Framework_TestCase;

class KinesisServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException MCP\Logger\Exception
     */
    public function testConstructAttemptsTooSmall()
    {
        $silent = false;
        $bufferLimit = 0;
        $attempts = -1;
        $shutdown = false;

        $kinesis = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder(JsonRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $service = new KinesisService($kinesis, $renderer, [
            KinesisService::CONFIG_IS_SILENT => $silent,
            KinesisService::CONFIG_BUFFER_LIMIT => $bufferLimit,
            KinesisService::CONFIG_KINESIS_ATTEMPTS => $attempts,
            KinesisService::CONFIG_REGISTER_SHUTDOWN => $shutdown
        ]);
    }

    /**
     * @expectedException MCP\Logger\Exception
     */
    public function testConstructBufferLimitSmall()
    {
        $silent = false;
        $bufferLimit = -1;
        $attempts = 1;
        $shutdown = false;

        $kinesis = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder(JsonRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $service = new KinesisService($kinesis, $renderer, [
            KinesisService::CONFIG_IS_SILENT => $silent,
            KinesisService::CONFIG_BUFFER_LIMIT => $bufferLimit,
            KinesisService::CONFIG_KINESIS_ATTEMPTS => $attempts,
            KinesisService::CONFIG_REGISTER_SHUTDOWN => $shutdown
        ]);
    }

    /**
     * @expectedException MCP\Logger\Exception
     */
    public function testSendTooLarge()
    {
        $data = str_repeat('a', KinesisService::SIZE_MAX+1);

        $silent = false;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $kinesis = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder(JsonRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->once())
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, [
            KinesisService::CONFIG_IS_SILENT => $silent,
            KinesisService::CONFIG_BUFFER_LIMIT => $bufferLimit,
            KinesisService::CONFIG_KINESIS_ATTEMPTS => $attempts,
            KinesisService::CONFIG_REGISTER_SHUTDOWN => $shutdown
        ]);

        $service->send($message);
    }

    public function testSendOneNoBuffer()
    {
        $data = 'abc123';
        $silent = false;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $messages = array_fill(0, 1, $message);

        $kinesis = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['putRecords'])
            ->getMock();

        $kinesis->expects($this->once())
            ->method('putRecords')
            ->with($this->anything())
            ->will($this->returnValue([
                'Records' => [
                    [
                        'SequenceNumber' => '',
                        'ShardId' => ''
                    ]
                ]
            ]));

        $renderer = $this->getMockBuilder(JsonRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(count($messages)))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, [
            KinesisService::CONFIG_IS_SILENT => $silent,
            KinesisService::CONFIG_BUFFER_LIMIT => $bufferLimit,
            KinesisService::CONFIG_KINESIS_ATTEMPTS => $attempts,
            KinesisService::CONFIG_REGISTER_SHUTDOWN => $shutdown
        ]);

        foreach ($messages as $message) {
            $service->send($message);
        }
    }

    /**
     * @expectedException MCP\Logger\Exception
     */
    public function testSendBadResponse()
    {
        $data = 'abc123';
        $silent = false;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $messages = array_fill(0, 1, $message);

        $kinesis = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['putRecords'])
            ->getMock();

        $kinesis->expects($this->once())
            ->method('putRecords')
            ->with($this->anything())
            ->will($this->returnValue([]));

        $renderer = $this->getMockBuilder(JsonRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(count($messages)))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, [
            KinesisService::CONFIG_IS_SILENT => $silent,
            KinesisService::CONFIG_BUFFER_LIMIT => $bufferLimit,
            KinesisService::CONFIG_KINESIS_ATTEMPTS => $attempts,
            KinesisService::CONFIG_REGISTER_SHUTDOWN => $shutdown
        ]);

        $service->send($message);
    }

    /**
     * @expectedException MCP\Logger\Exception
     */
    public function testSendLeftovers()
    {
        $data = 'abc123';
        $silent = false;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $messages = array_fill(0, 1, $message);

        $kinesis = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['putRecords'])
            ->getMock();

        $kinesis->expects($this->once())
            ->method('putRecords')
            ->with($this->anything())
            ->will($this->returnValue([
                'Records' => [
                    [
                        'ErrorCode' => '',
                        'ErrorMessage' => ''
                    ]
                ]
            ]));

        $renderer = $this->getMockBuilder(JsonRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(count($messages)))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, [
            KinesisService::CONFIG_IS_SILENT => $silent,
            KinesisService::CONFIG_BUFFER_LIMIT => $bufferLimit,
            KinesisService::CONFIG_KINESIS_ATTEMPTS => $attempts,
            KinesisService::CONFIG_REGISTER_SHUTDOWN => $shutdown
        ]);

        $service->send($message);
    }

    /**
     * @expectedException MCP\Logger\Exception
     */
    public function testSendUnknownError()
    {
        $data = 'abc123';
        $silent = false;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $messages = array_fill(0, 1, $message);

        $kinesis = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['putRecords'])
            ->getMock();

        $exception = $this->getMockBuilder('Aws\Kinesis\Exception\KinesisException')
            ->disableOriginalConstructor()
            ->getMock();

        $kinesis->expects($this->once())
            ->method('putRecords')
            ->with($this->anything())
            ->will($this->throwException($exception));

        $renderer = $this->getMockBuilder(JsonRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(count($messages)))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, [
            KinesisService::CONFIG_IS_SILENT => $silent,
            KinesisService::CONFIG_BUFFER_LIMIT => $bufferLimit,
            KinesisService::CONFIG_KINESIS_ATTEMPTS => $attempts,
            KinesisService::CONFIG_REGISTER_SHUTDOWN => $shutdown
        ]);

        $service->send($message);
    }

    /**
     * @expectedException MCP\Logger\Exception
     */
    public function testSendMultipleErrors()
    {
        $data = str_repeat('a', KinesisService::SIZE_MAX+1);
        $silent = false;
        $bufferLimit = 3;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $kinesis = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder(JsonRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(2))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, [
            KinesisService::CONFIG_IS_SILENT => $silent,
            KinesisService::CONFIG_BUFFER_LIMIT => $bufferLimit,
            KinesisService::CONFIG_KINESIS_ATTEMPTS => $attempts,
            KinesisService::CONFIG_REGISTER_SHUTDOWN => $shutdown
        ]);

        $service->send($message);
        $service->send($message);
        $service->flush();
    }

    public function testSendSilent()
    {
        touch(__DIR__ . '/errlog');
        $log_location = ini_get('error_log');
        ini_set('error_log', __DIR__ . '/errlog');

        ///////

        $data = str_repeat('a', KinesisService::SIZE_MAX+1);

        $silent = true;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $kinesis = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder(JsonRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->once())
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, [
            KinesisService::CONFIG_IS_SILENT => $silent,
            KinesisService::CONFIG_BUFFER_LIMIT => $bufferLimit,
            KinesisService::CONFIG_KINESIS_ATTEMPTS => $attempts,
            KinesisService::CONFIG_REGISTER_SHUTDOWN => $shutdown
        ]);

        $service->send($message);

        ///////

        $this->assertContains('Log message exceeds 1MB in size.', file_get_contents(__DIR__ . '/errlog'));

        ini_set('error_log', $log_location);
        unlink(__DIR__ . '/errlog');
    }
}
