<?php
declare(strict_types=1);
namespace Viserio\Container\Tests;

use ReflectionClass;
use StdClass;
use Viserio\Container\MockContainer;

class MockerContainerTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    /**
     * @var array
     */
    private $services = [];

    public function setUp()
    {
        $this->container = new MockContainer();
        $this->services = ['test.service_1' => null, 'test.service_2' => null, 'test.service_3' => null];

        foreach (array_keys($this->services) as $id) {
            $service = new StdClass();
            $service->id = $id;

            $this->services[$id] = $service;
            $this->container->instance($id, $service);
        }
    }

    /**
     * As the mocks are never cleared during the execution
     * we have to do it manually.
     */
    public function tearDown()
    {
        $reflection = new ReflectionClass(MockContainer::class);

        $property = $reflection->getProperty('mockedServices');
        $property->setAccessible(true);
        $property->setValue($reflection, []);
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
        $mock = $this->container->mock('test.service_1', StdClass::class);

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
        $this->container->mock('test.new_service', StdClass::class);
    }

    public function testThatMockedServicesAreAccessible()
    {
        $mock1 = $this->container->mock('test.service_1', StdClass::class);
        $mock2 = $this->container->mock('test.service_2', StdClass::class);
        $mockedServices = $this->container->getMockedServices();

        $this->assertEquals(['mock::test.service_1' => $mock1, 'mock::test.service_2' => $mock2], $mockedServices);
    }

    public function testThatServiceCanBeMockedOnce()
    {
        $mock1 = $this->container->mock('test.service_1', StdClass::class);
        $mock2 = $this->container->mock('test.service_1', StdClass::class);

        $this->assertSame($mock1, $mock2);
        $this->assertSame($mock2, $this->container->get('test.service_1'));
    }

    public function testThatMockCanBeRemovedAndContainerFallsBackToTheOriginalService()
    {
        $mock = $this->container->mock('test.service_1', StdClass::class);
        $this->container->unmock('test.service_1');

        $this->assertTrue($this->container->has('test.service_1'));
        $this->assertEquals($this->services['test.service_1'], $this->container->get('test.service_1'));
    }
}
