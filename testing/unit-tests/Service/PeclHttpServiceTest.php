<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger\Service;

use HttpException;
use Mockery;
use PHPUnit_Framework_TestCase;

class PeclHttpServiceTest extends PHPUnit_Framework_TestCase
{
    public static $logSetting;
    public $uri;

    public static function setUpBeforeClass()
    {
        self::$logSetting = ini_get('error_log');
        ini_set('error_log', 'syslog');
    }

    public static function tearDownAfterClass()
    {
        ini_set('error_log', self::$logSetting);
    }

    public function setUp()
    {
        $this->uri = Mockery::mock('QL\UriTemplate\UriTemplate', [
            'expand' => 'http://corelogger'
        ]);
    }

    /**
     * Note: This test uses PHP Unit Mock Builder because HttpRequest/HttpMessage are not compatible with Mockery.
     *
     * @expectedException MCP\Logger\Exception
     * @expectedExceptionMessage The service responded with an unexpected http code: '404'
     */
    public function testServiceReceivesNon200ResponseThrowsException()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        // expectations
        $response = $this->getMockBuilder('HttpMessage')->getMock();
        $response
            ->expects($this->exactly(2))
            ->method('getResponseCode')
            ->will($this->returnValue('404'));

        $request = $this->buildMockRequest();
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $renderer
            ->shouldReceive('__invoke')
            ->once()
            ->with($message)
            ->andReturn('rendered message');

        $service = new PeclHttpService($request, $renderer, $this->uri, false);
        $service->send($message);
    }

    /**
     * Note: This test uses PHP Unit Mock Builder because HttpRequest/HttpMessage are not compatible with Mockery.
     *
     * @expectedException HttpException
     * @expectedExceptionMessage msg
     */
    public function testExceptionIsNotCaughtWhenServiceThrowsHttpException()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        // expectations
        $response = $this->getMockBuilder('HttpMessage')->getMock();
        $response
            ->expects($this->never())
            ->method('getResponseCode');

        $request = $this->buildMockRequest();
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->throwException(new HttpException('msg')));

        $renderer
            ->shouldReceive('__invoke')
            ->once()
            ->with($message)
            ->andReturn('rendered message');

        $service = new PeclHttpService($request, $renderer, $this->uri, false);
        $this->assertNull($service->send($message));
    }

    /**
     * Note: This test uses PHP Unit Mock Builder because HttpRequest/HttpMessage are not compatible with Mockery.
     */
    public function testServiceReceivesNon200ResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        // expectations
        $response = $this->getMockBuilder('HttpMessage')->getMock();
        $response
            ->expects($this->never())
            ->method('getResponseCode');

        $request = $this->buildMockRequest();
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $renderer
            ->shouldReceive('__invoke')
            ->once()
            ->with($message)
            ->andReturn('rendered message');

        $service = new PeclHttpService($request, $renderer, $this->uri, true);
        $this->assertNull($service->send($message));
    }

    /**
     * Note: This test uses PHP Unit Mock Builder because HttpRequest/HttpMessage are not compatible with Mockery.
     */
    public function testServiceThrowsHttpExceptionResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Logger\MessageInterface');

        // expectations
        $response = $this->getMockBuilder('HttpMessage')->getMock();
        $response
            ->expects($this->never())
            ->method('getResponseCode');

        $request = $this->buildMockRequest();
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->throwException(new HttpException('msg')));

        $renderer
            ->shouldReceive('__invoke')
            ->once()
            ->with($message)
            ->andReturn('rendered message');

        $service = new PeclHttpService($request, $renderer, $this->uri, true);
        $this->assertNull($service->send($message));
    }

    /**
     * Build Mock Object HttpRequest
     */
    public function buildMockRequest()
    {
        $request = $this
            ->getMockBuilder('HttpRequest')
            ->getMock();

        $request
            ->expects($this->once())
            ->method('setMethod');

        $request
            ->expects($this->once())
            ->method('setContentType')
            ->with('text/xml');

        $request
            ->expects($this->once())
            ->method('setBody')
            ->will($this->returnValue('rendered message'));

        return $request;
    }
}
