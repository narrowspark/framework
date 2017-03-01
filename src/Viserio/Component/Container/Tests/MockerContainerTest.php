<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Viserio\Component\Container\MockContainer;

class MockerContainerTest extends TestCase
{
    private $container;

    /**
     * @var array
     */
    private $services = [];

    public function setUp()
    {
        $this->container = new MockContainer();
        $this->services  = ['test.service_1' => null, 'test.service_2' => null, 'test.service_3' => null];

        foreach (array_keys($this->services) as $id) {
            $service     = new stdClass();
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

        parent::tearDown();
    }

    public function testThatBehaviorDoesNotChangeByDefault()
    {
        self::assertTrue($this->container->has('test.service_1'));
        self::assertTrue($this->container->has('test.service_2'));
        self::assertTrue($this->container->has('test.service_3'));

        self::assertSame($this->services['test.service_1'], $this->container->get('test.service_1'));
        self::assertSame($this->services['test.service_2'], $this->container->get('test.service_2'));
        self::assertSame($this->services['test.service_3'], $this->container->get('test.service_3'));
    }

    public function testThatServiceCanBeMocked()
    {
        $mock = $this->container->mock('test.service_1', stdClass::class);

        self::assertTrue($this->container->has('test.service_1'));
        self::assertNotSame($this->services['test.service_1'], $mock);
        self::assertSame($mock, $this->container->get('test.service_1'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot mock a non-existent service: [test.new_service]
     */
    public function testThatServiceCannotBeMockedIfItDoesNotExist()
    {
        $this->container->mock('test.new_service', stdClass::class);
    }

    public function testThatMockedServicesAreAccessible()
    {
        $mock1          = $this->container->mock('test.service_1', stdClass::class);
        $mock2          = $this->container->mock('test.service_2', stdClass::class);
        $mockedServices = $this->container->getMockedServices();

        self::assertEquals(['mock::test.service_1' => $mock1, 'mock::test.service_2' => $mock2], $mockedServices);
    }

    public function testThatServiceCanBeMockedOnce()
    {
        $mock1 = $this->container->mock('test.service_1', stdClass::class);
        $mock2 = $this->container->mock('test.service_1', stdClass::class);

        self::assertSame($mock1, $mock2);
        self::assertSame($mock2, $this->container->get('test.service_1'));
    }

    public function testThatMockCanBeRemovedAndContainerFallsBackToTheOriginalService()
    {
        $mock = $this->container->mock('test.service_1', stdClass::class);
        $this->container->unmock('test.service_1');

        self::assertTrue($this->container->has('test.service_1'));
        self::assertEquals($this->services['test.service_1'], $this->container->get('test.service_1'));
    }
}
