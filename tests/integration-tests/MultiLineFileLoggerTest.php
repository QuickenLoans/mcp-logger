<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use PHPUnit\Framework\TestCase;
use QL\MCP\Common\GUID;
use QL\MCP\Logger\Serializer\LineSerializer;

class MultiLineFileLoggerTest extends TestCase
{
    public static $logSetting;

    public $template = <<<TEXT_TEMPLATE
[{{ shortid }}] --------------------------------------------------------------------------------
[{{ shortid }}] {{ severity }} : {{ message }}
[{{ shortid }}] --------------------------------------------------------------------------------
{{ details }}

TEXT_TEMPLATE;
    public $exampleStacktrace = <<<TEXT_TEMPLATE
QL\MCP\Logger\Exception: this is a test exception in /project/tests/integration-tests/MultiLineFileLoggerTest.php:50
Stack trace:
#0 [internal function]: QL\MCP\Logger\DefaultLoggerTest->test()
#1 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(1072): ReflectionMethod->invokeArgs(Object, Array)
#2 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(940): PHPUnit\Framework\TestCase->runTest()
TEXT_TEMPLATE;

    public static function setUpBeforeClass()
    {
        self::$logSetting = ini_get('error_log');
        ini_set('error_log', __DIR__ . '/errlog');
    }

    public static function tearDownAfterClass()
    {
        ini_set('error_log', self::$logSetting);
    }

    public function tearDown()
    {
        @unlink(__DIR__ . '/errlog');
    }

    public function test()
    {
        $serializer = new LineSerializer(['template' => $this->template]);
        $serializer->withFlag('ALLOW_NEWLINES');

        $logger = new Logger(null, $serializer);
        $logger->withFlag('SPLIT_ON_NEWLINES');

        $context = [
            'testvar' => 1234,
            'details' => $this->exampleStacktrace
        ];

        $logger->emergency('test message - alfa',   $context + ['id' => GUID::createFromHex('1111E37AD36C4F88B142A417670C93CF')]);
        $logger->alert('test message - bravo',      $context + ['id' => GUID::createFromHex('2222E37AD36C4F88B142A417670C93CF')]);
        $logger->critical('test message - charlie', $context + ['id' => GUID::createFromHex('3333E37AD36C4F88B142A417670C93CF')]);
        $logger->error('test message - delta',      $context + ['id' => GUID::createFromHex('4444E37AD36C4F88B142A417670C93CF')]);
        $logger->warning('test message - echo',     $context + ['id' => GUID::createFromHex('5555E37AD36C4F88B142A417670C93CF')]);
        $logger->notice('test message - foxtrot',   $context + ['id' => GUID::createFromHex('6666E37AD36C4F88B142A417670C93CF')]);
        $logger->info('test message - golf',        $context + ['id' => GUID::createFromHex('7777E37AD36C4F88B142A417670C93CF')]);
        $logger->debug('test message - hotel',      $context + ['id' => GUID::createFromHex('8888E37AD36C4F88B142A417670C93CF')]);

        $actual = file_get_contents(__DIR__ . '/errlog');

        $expectedLines = <<<EXPECTED_TEXT
[1111e37a] --------------------------------------------------------------------------------
[1111e37a] emergency : test message - alfa
[1111e37a] --------------------------------------------------------------------------------
QL\MCP\Logger\Exception: this is a test exception in /project/tests/integration-tests/MultiLineFileLoggerTest.php:50
Stack trace:
#0 [internal function]: QL\MCP\Logger\DefaultLoggerTest->test()
#1 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(1072): ReflectionMethod->invokeArgs(Object, Array)
#2 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(940): PHPUnit\Framework\TestCase->runTest()
[1111e37a]
[2222e37a] --------------------------------------------------------------------------------
[2222e37a] alert : test message - bravo
[2222e37a] --------------------------------------------------------------------------------
QL\MCP\Logger\Exception: this is a test exception in /project/tests/integration-tests/MultiLineFileLoggerTest.php:50
Stack trace:
#0 [internal function]: QL\MCP\Logger\DefaultLoggerTest->test()
#1 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(1072): ReflectionMethod->invokeArgs(Object, Array)
#2 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(940): PHPUnit\Framework\TestCase->runTest()
[2222e37a]
[3333e37a] --------------------------------------------------------------------------------
[3333e37a] critical : test message - charlie
[3333e37a] --------------------------------------------------------------------------------
QL\MCP\Logger\Exception: this is a test exception in /project/tests/integration-tests/MultiLineFileLoggerTest.php:50
Stack trace:
#0 [internal function]: QL\MCP\Logger\DefaultLoggerTest->test()
#1 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(1072): ReflectionMethod->invokeArgs(Object, Array)
#2 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(940): PHPUnit\Framework\TestCase->runTest()
[3333e37a]
[4444e37a] --------------------------------------------------------------------------------
[4444e37a] error : test message - delta
[4444e37a] --------------------------------------------------------------------------------
QL\MCP\Logger\Exception: this is a test exception in /project/tests/integration-tests/MultiLineFileLoggerTest.php:50
Stack trace:
#0 [internal function]: QL\MCP\Logger\DefaultLoggerTest->test()
#1 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(1072): ReflectionMethod->invokeArgs(Object, Array)
#2 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(940): PHPUnit\Framework\TestCase->runTest()
[4444e37a]
[5555e37a] --------------------------------------------------------------------------------
[5555e37a] warning : test message - echo
[5555e37a] --------------------------------------------------------------------------------
QL\MCP\Logger\Exception: this is a test exception in /project/tests/integration-tests/MultiLineFileLoggerTest.php:50
Stack trace:
#0 [internal function]: QL\MCP\Logger\DefaultLoggerTest->test()
#1 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(1072): ReflectionMethod->invokeArgs(Object, Array)
#2 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(940): PHPUnit\Framework\TestCase->runTest()
[5555e37a]
[6666e37a] --------------------------------------------------------------------------------
[6666e37a] notice : test message - foxtrot
[6666e37a] --------------------------------------------------------------------------------
QL\MCP\Logger\Exception: this is a test exception in /project/tests/integration-tests/MultiLineFileLoggerTest.php:50
Stack trace:
#0 [internal function]: QL\MCP\Logger\DefaultLoggerTest->test()
#1 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(1072): ReflectionMethod->invokeArgs(Object, Array)
#2 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(940): PHPUnit\Framework\TestCase->runTest()
[6666e37a]
[7777e37a] --------------------------------------------------------------------------------
[7777e37a] info : test message - golf
[7777e37a] --------------------------------------------------------------------------------
QL\MCP\Logger\Exception: this is a test exception in /project/tests/integration-tests/MultiLineFileLoggerTest.php:50
Stack trace:
#0 [internal function]: QL\MCP\Logger\DefaultLoggerTest->test()
#1 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(1072): ReflectionMethod->invokeArgs(Object, Array)
#2 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(940): PHPUnit\Framework\TestCase->runTest()
[7777e37a]
[8888e37a] --------------------------------------------------------------------------------
[8888e37a] debug : test message - hotel
[8888e37a] --------------------------------------------------------------------------------
QL\MCP\Logger\Exception: this is a test exception in /project/tests/integration-tests/MultiLineFileLoggerTest.php:50
Stack trace:
#0 [internal function]: QL\MCP\Logger\DefaultLoggerTest->test()
#1 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(1072): ReflectionMethod->invokeArgs(Object, Array)
#2 /project/vendor/phpunit/phpunit/src/Framework/TestCase.php(940): PHPUnit\Framework\TestCase->runTest()
[8888e37a]
EXPECTED_TEXT;

        foreach (explode("\n", $expectedLines) as $line) {
            $this->assertContains($line, $actual);
        }
    }
}
