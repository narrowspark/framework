<?php

namespace Brainwave\Container\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.8-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Container\Container;

/**
 * MockerContainerTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8-dev
 */
class MockerContainerTest extends \PHPUnit_Framework_TestCase
{
    private $container = null;

    /**
     * @var array
     */
    private $services = [];

    public function setUp()
    {
        $this->container = new Container();
        $this->services = ['test.service_1' => null, 'test.service_2' => null, 'test.service_3' => null];

        foreach (array_keys($this->services) as $id) {
            $service = new \stdClass();
            $service->id = $id;
            $this->services[$id] = $service;

            $this->container->set($id, $service);
        }
    }

    /**
     * As the mocks are never cleared during the execution
     * we have to do it manually.
     */
    public function tearDown()
    {
        $reflection = new \ReflectionClass('Brainwave\Container\Container');

        $property = $reflection->getProperty('mockedServices');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    public function testThatBehaviorDoesNotChangeByDefault()
    {
        $this->assertTrue($this->container->has('test.service_1'));
        $this->assertTrue($this->container->has('test.service_2'));
        $this->assertTrue($this->container->has('test.service_3'));
        $this->assertSame($this->services['test.service_1'], $this->container->get('test.service_1'));
        $this->assertSame($this->services['test.service_2'], $this->container->get('test.service_2'));
        $this->assertSame($this->services['test.service_3'], $this->container->get('test.service_3'));
    }

    public function testThatServiceCanBeMocked()
    {
        $mock = $this->container->mock('test.service_1', 'stdClass');
        $this->assertTrue($this->container->has('test.service_1'));
        $this->assertNotSame($this->services['test.service_1'], $mock);
        $this->assertSame($mock, $this->container->get('test.service_1'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot mock a non-existent service: "test.new_service"
     */
    public function testThatServiceCannotBeMockedIfItDoesNotExist()
    {
        $this->container->mock('test.new_service', 'stdClass');
    }

    public function testThatMultipleInstancesShareMockedServices()
    {
        $mock = $this->container->mock('test.service_1', 'stdClass');
        $secondContainer = new Container();
        $this->assertTrue($secondContainer->has('test.service_1'));
        $this->assertFalse($secondContainer->has('test.service_2'));
        $this->assertFalse($secondContainer->has('test.service_3'));
        $this->assertSame($mock, $secondContainer->get('test.service_1'));
    }

    public function testThatMockedServicesAreAccessible()
    {
        $mock1 = $this->container->mock('test.service_1', 'stdClass');
        $mock2 = $this->container->mock('test.service_2', 'stdClass');
        $mockedServices = $this->container->getMockedServices();
        $this->assertEquals(['test.service_1' => $mock1, 'test.service_2' => $mock2], $mockedServices);
    }

    public function testThatServiceCanBeMockedOnce()
    {
        $mock1 = $this->container->mock('test.service_1', 'stdClass');
        $mock2 = $this->container->mock('test.service_1', 'stdClass');
        $this->assertSame($mock1, $mock2);
        $this->assertSame($mock2, $this->container->get('test.service_1'));
    }

    public function testThatMockCanBeRemovedAndContainerFallsBackToTheOriginalService()
    {
        $mock = $this->container->mock('test.service_1', 'stdClass');
        $this->container->unmock('test.service_1');
        $this->assertTrue($this->container->has('test.service_1'));
        $this->assertEquals($this->services['test.service_1'], $this->container->get('test.service_1'));
    }
}
