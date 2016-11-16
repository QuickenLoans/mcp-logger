<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Message;

use stdClass;
use MCP\Testing\TestCase;

/**
 * @covers MCP\Service\Logger\Message\Message
 * @covers MCP\Service\Logger\Message\MessageLoadingTrait
 */
class MessageTest extends TestCase
{
    /**
     * @dataProvider providerMissingRequiredFields
     */
    public function testMissingParameterThrowsException($missingField)
    {
        $this->setExpectedException('BadFunctionCallException', sprintf("'%s' is required", $missingField));
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture[$missingField] = null;

        $message = new Message($fixture);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'machineIPAddress' must be an instance of 'MCP\DataType\IPv4Address'
     */
    public function testInvalidClassTypeThrowsException()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['machineIPAddress'] = 'tacos';

        $message = new Message($fixture);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'extendedProperties' must be an instance of 'array'
     */
    public function testInvalidExtendedPropertiesThrowsException()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = 'tacos';

        $message = new Message($fixture);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Extended Properties must use named keys
     */
    public function testIndexedExtendedPropertiesThrowsException()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = array('tacos');

        $message = new Message($fixture);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Extended Properties must not nest arrays
     */
    public function testNestedArrayInExtendedPropertiesThrowsException()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = array('key' => array('tacos'));

        $message = new Message($fixture);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Extended Properties must be scalars or objects that implement __toString
     */
    public function testInvalidObjectInExtendedPropertiesThrowsException()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['extendedProperties'] = array('key' => new stdClass);

        $message = new Message($fixture);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage 'doritos locos' is not a valid log level.
     */
    public function testInvalidLevelThrowsException()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixture['level'] = 'doritos locos';

        $message = new Message($fixture);
    }

    public function testAccessorsForMinimumProperties()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');

        $message = new Message($fixture);
        foreach ($fixture as $accessor => $expected) {
            $this->assertSame($expected, $message->$accessor(), $accessor);
        }
    }

    public function testAccessorsForDefaultProperties()
    {
        $fixture = $this->loadPhpFixture('minimum-properties.phpd');
        $fixtureOut = $this->loadPhpFixture('default-properties.phpd');

        $message = new Message($fixture);
        foreach ($fixtureOut as $accessor => $expected) {
            $this->assertEquals($expected, $message->$accessor(), $accessor);
        }
    }

    public function testAccessorsForAllProperties()
    {
        $fixture = $this->loadPhpFixture('all-properties.phpd');

        $message = new Message($fixture);
        foreach ($fixture as $accessor => $expected) {
            $this->assertSame($expected, $message->$accessor(), $accessor);
        }
    }

    public function providerMissingRequiredFields()
    {
        return array(
            array('applicationId'),
            array('createTime'),
            array('machineName'),
            array('message')
        );
    }
}
