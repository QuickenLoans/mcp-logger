<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Service;

use HttpException;
use Mockery;
use PHPUnit_Framework_TestCase;

class HttpServiceTest extends PHPUnit_Framework_TestCase
{
    public static $logSetting;

    public static function setUpBeforeClass()
    {
        self::$logSetting = ini_get('error_log');
        ini_set('error_log', 'syslog');
    }

    public static function tearDownAfterClass()
    {
        ini_set('error_log', self::$logSetting);
    }

    /**
     * @expectedException MCP\Service\Logger\Exception
     * @expectedExceptionMessage The HttpRequest object is missing a url
     */
    public function testMissingUrlThrowsException()
    {
        $request = Mockery::mock('HttpRequest', array('getUrl' => ''));
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');

        $service = new HttpService($request, $renderer);
    }

    /**
     * Note: This test uses PHP Unit Mock Builder because HttpRequest/HttpMessage are
     * not compatible with Mockery.
     *
     * @expectedException MCP\Service\Logger\Exception
     * @expectedExceptionMessage The service responded with an unexpected http code: '404'
     */
    public function testServiceReceivesNon200ResponseThrowsException()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

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

        $service = new HttpService($request, $renderer);
        $service->send($message);
    }

    /**
     * Note: This test uses PHP Unit Mock Builder because HttpRequest/HttpMessage are
     * not compatible with Mockery.
     *
     * @expectedException HttpException
     * @expectedExceptionMessage msg
     */
    public function testExceptionIsNotCaughtWhenServiceThrowsHttpException()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

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

        $service = new HttpService($request, $renderer);
        $this->assertNull($service->send($message));
    }

    /**
     * Note: This test uses PHP Unit Mock Builder because HttpRequest/HttpMessage are
     * not compatible with Mockery.
     */
    public function testServiceReceivesNon200ResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

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

        $service = new HttpService($request, $renderer, true);
        $this->assertNull($service->send($message));
    }

    /**
     * Note: This test uses PHP Unit Mock Builder because HttpRequest/HttpMessage are
     * not compatible with Mockery.
     */
    public function testServiceThrowsHttpExceptionResponseReturnsNullWhenSilent()
    {
        $renderer = Mockery::mock('MCP\Service\Logger\RendererInterface');
        $message = Mockery::mock('MCP\Service\Logger\MessageInterface');

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

        $service = new HttpService($request, $renderer, true);
        $this->assertNull($service->send($message));
    }

    /**
     * Build Mock Object HttpRequest
     */
    public function buildMockRequest()
    {
        $request = $this->getMockBuilder('HttpRequest')->getMock();
        $request
            ->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue('http://service'));

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
