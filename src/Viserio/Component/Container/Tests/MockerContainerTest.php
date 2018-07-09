<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Viserio\Component\Container\MockContainer;

/**
 * @internal
 */
final class MockerContainerTest extends TestCase
{
    /**
     * @var \Viserio\Component\Container\MockContainer
     */
    private $container;

    /**
     * @var array
     */
    private $services = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new MockContainer();
        $this->services  = ['test.service_1' => null, 'test.service_2' => null, 'test.service_3' => null];

        foreach (\array_keys($this->services) as $id) {
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
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(MockContainer::class);

        $property = $reflection->getProperty('mockedServices');
        $property->setAccessible(true);
        $property->setValue($reflection, []);

        parent::tearDown();
    }

    public function testThatBehaviorDoesNotChangeByDefault(): void
    {
        static::assertTrue($this->container->has('test.service_1'));
        static::assertTrue($this->container->has('test.service_2'));
        static::assertTrue($this->container->has('test.service_3'));

        static::assertSame($this->services['test.service_1'], $this->container->get('test.service_1'));
        static::assertSame($this->services['test.service_2'], $this->container->get('test.service_2'));
        static::assertSame($this->services['test.service_3'], $this->container->get('test.service_3'));
    }

    public function testThatServiceCanBeMocked(): void
    {
        $mock = $this->container->mock('test.service_1', stdClass::class);

        static::assertTrue($this->container->has('test.service_1'));
        static::assertNotSame($this->services['test.service_1'], $mock);
        static::assertSame($mock, $this->container->get('test.service_1'));
    }

    public function testThatServiceCannotBeMockedIfItDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot mock a non-existent service: [test.new_service]');

        $this->container->mock('test.new_service', stdClass::class);
    }

    public function testThatMockedServicesAreAccessible(): void
    {
        $mock1          = $this->container->mock('test.service_1', stdClass::class);
        $mock2          = $this->container->mock('test.service_2', stdClass::class);
        $mockedServices = $this->container->getMockedServices();

        static::assertEquals(['mock::test.service_1' => $mock1, 'mock::test.service_2' => $mock2], $mockedServices);
    }

    public function testThatServiceCanBeMockedOnce(): void
    {
        $mock1 = $this->container->mock('test.service_1', stdClass::class);
        $mock2 = $this->container->mock('test.service_1', stdClass::class);

        static::assertSame($mock1, $mock2);
        static::assertSame($mock2, $this->container->get('test.service_1'));
    }

    public function testThatMockCanBeRemovedAndContainerFallsBackToTheOriginalService(): void
    {
        $this->container->mock('test.service_1', stdClass::class);
        $this->container->unmock('test.service_1');

        static::assertTrue($this->container->has('test.service_1'));
        static::assertEquals($this->services['test.service_1'], $this->container->get('test.service_1'));
    }
}
