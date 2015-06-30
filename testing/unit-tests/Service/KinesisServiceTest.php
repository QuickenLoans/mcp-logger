<?php

namespace MCP\Logger\Service;

class KinesisServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testConstructAttemptsTooSmall()
    {
        $silent = false;
        $stream = KinesisService::STREAM_DEFAULT;
        $bufferLimit = 0;
        $attempts = -1;
        $shutdown = false;

        $kinesis = $this->getMockBuilder('Aws\Kinesis\KinesisClient')
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder('MCP\Logger\Renderer\JsonRenderer')
            ->disableOriginalConstructor()
            ->getMock();

        $service = new KinesisService($kinesis, $renderer, $silent, $stream, $bufferLimit, $attempts, $shutdown);
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testConstructBufferLimitSmall()
    {
        $silent = false;
        $stream = KinesisService::STREAM_DEFAULT;
        $bufferLimit = -1;
        $attempts = 1;
        $shutdown = false;

        $kinesis = $this->getMockBuilder('Aws\Kinesis\KinesisClient')
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder('MCP\Logger\Renderer\JsonRenderer')
            ->disableOriginalConstructor()
            ->getMock();

        $service = new KinesisService($kinesis, $renderer, $silent, $stream, $bufferLimit, $attempts, $shutdown);
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testSendTooLarge()
    {
        $data = str_repeat('a', KinesisService::SIZE_MAX+1);

        $silent = false;
        $stream = KinesisService::STREAM_DEFAULT;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder('MCP\Logger\Message\Message')
            ->disableOriginalConstructor()
            ->getMock();

        $kinesis = $this->getMockBuilder('Aws\Kinesis\KinesisClient')
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder('MCP\Logger\Renderer\JsonRenderer')
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->once())
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, $silent, $stream, $bufferLimit, $attempts, $shutdown);
        $service->send($message);
    }

    public function testSendOneNoBuffer()
    {
        $data = 'abc123';
        $silent = false;
        $stream = KinesisService::STREAM_DEFAULT;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder('MCP\Logger\Message\Message')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $messages = array_fill(0, 1, $message);

        $kinesis = $this->getMockBuilder('Aws\Kinesis\KinesisClient')
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

        $renderer = $this->getMockBuilder('MCP\Logger\Renderer\JsonRenderer')
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(count($messages)))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, $silent, $stream, $bufferLimit, $attempts, $shutdown);

        foreach ($messages as $message) {
            $service->send($message);
        }
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testSendBadResponse()
    {
        $data = 'abc123';
        $silent = false;
        $stream = KinesisService::STREAM_DEFAULT;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder('MCP\Logger\Message\Message')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $messages = array_fill(0, 1, $message);

        $kinesis = $this->getMockBuilder('Aws\Kinesis\KinesisClient')
            ->disableOriginalConstructor()
            ->setMethods(['putRecords'])
            ->getMock();

        $kinesis->expects($this->once())
            ->method('putRecords')
            ->with($this->anything())
            ->will($this->returnValue([]));

        $renderer = $this->getMockBuilder('MCP\Logger\Renderer\JsonRenderer')
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(count($messages)))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, $silent, $stream, $bufferLimit, $attempts, $shutdown);
        $service->send($message);
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testSendLeftovers()
    {
        $data = 'abc123';
        $silent = false;
        $stream = KinesisService::STREAM_DEFAULT;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder('MCP\Logger\Message\Message')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $messages = array_fill(0, 1, $message);

        $kinesis = $this->getMockBuilder('Aws\Kinesis\KinesisClient')
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

        $renderer = $this->getMockBuilder('MCP\Logger\Renderer\JsonRenderer')
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(count($messages)))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, $silent, $stream, $bufferLimit, $attempts, $shutdown);
        $service->send($message);
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testSendUnknownError()
    {
        $data = 'abc123';
        $silent = false;
        $stream = KinesisService::STREAM_DEFAULT;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder('MCP\Logger\Message\Message')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $messages = array_fill(0, 1, $message);

        $kinesis = $this->getMockBuilder('Aws\Kinesis\KinesisClient')
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

        $renderer = $this->getMockBuilder('MCP\Logger\Renderer\JsonRenderer')
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(count($messages)))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, $silent, $stream, $bufferLimit, $attempts, $shutdown);
        $service->send($message);
    }

    /**
     * @expectedException \MCP\Logger\Exception
     */
    public function testSendMultipleErrors()
    {
        $data = str_repeat('a', KinesisService::SIZE_MAX+1);
        $silent = false;
        $stream = KinesisService::STREAM_DEFAULT;
        $bufferLimit = 3;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder('MCP\Logger\Message\Message')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $kinesis = $this->getMockBuilder('Aws\Kinesis\KinesisClient')
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder('MCP\Logger\Renderer\JsonRenderer')
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->exactly(2))
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, $silent, $stream, $bufferLimit, $attempts, $shutdown);
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
        $stream = KinesisService::STREAM_DEFAULT;
        $bufferLimit = 0;
        $attempts = 1;
        $shutdown = false;

        $message = $this->getMockBuilder('MCP\Logger\Message\Message')
            ->disableOriginalConstructor()
            ->getMock();

        $kinesis = $this->getMockBuilder('Aws\Kinesis\KinesisClient')
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder('MCP\Logger\Renderer\JsonRenderer')
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $renderer->expects($this->once())
            ->method('__invoke')
            ->with($message)
            ->will($this->returnValue($data));

        $service = new KinesisService($kinesis, $renderer, $silent, $stream, $bufferLimit, $attempts, $shutdown);
        $service->send($message);

        ///////

        $this->assertContains('Log message exceeds 1MB in size.', file_get_contents(__DIR__ . '/errlog'));

        ini_set('error_log', $log_location);
        unlink(__DIR__ . '/errlog');
    }
}