<?php
declare(strict_types=1);
//declare(strict_types=1);
//namespace Viserio\Component\Container\Tests;
//
//use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
//use stdClass;
//use Viserio\Component\Container\Container;
//use Viserio\Component\Container\Tests\Fixture\ContainerConcreteFixture;
//use Viserio\Component\Container\Tests\Fixture\ContainerContractFixtureInterface;
//use Viserio\Component\Container\Tests\Fixture\ContainerImplementationFixture;
//use Viserio\Component\Container\Tests\Fixture\ContainerImplementationTwoFixture;
//use Viserio\Component\Container\Tests\Fixture\ContainerInjectVariableFixture;
//use Viserio\Component\Container\Tests\Fixture\ContainerTestContextInjectInstantiationsFixture;
//use Viserio\Component\Container\Tests\Fixture\ContainerTestContextInjectOneFixture;
//use Viserio\Component\Container\Tests\Fixture\ContainerTestContextInjectTwoFixture;
//use Viserio\Component\Container\Tests\Fixture\ContainerTestNoConstructor;
//
///**
// * @internal
// */
//final class ContainerTest extends MockeryTestCase
//{
//    /**
//     * @var \Viserio\Component\Container\Container
//     */
//    protected $container;
//
//    /**
//     * @var array
//     */
//    protected $services = [];
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function setUp(): void
//    {
//        parent::setUp();
//
//        $this->container = new Container();
//        $this->services  = ['test.service_1' => null, 'test.service_2' => null, 'test.service_3' => null];
//
//        foreach (\array_keys($this->services) as $id) {
//            $service     = new stdClass();
//            $service->id = $id;
//
//            $this->services[$id] = $service;
//            $this->container->bind($id, $service);
//        }
//    }
//
//    public function testContainerCanInjectSimpleVariable(): void
//    {
//        $this->container->when(ContainerInjectVariableFixture::class)
//            ->needs('$something')
//            ->give(100);
//        $instance = $this->container->make(ContainerInjectVariableFixture::class);
//
//        static::assertEquals(100, $instance->something);
//
//        $this->container->when(ContainerInjectVariableFixture::class)
//            ->needs('$something')->give(function ($container) {
//                return $this->container->make(ContainerConcreteFixture::class);
//            });
//
//        $instance = $this->container->make(ContainerInjectVariableFixture::class);
//
//        static::assertInstanceOf(ContainerConcreteFixture::class, $instance->something);
//    }
//
//    public function testContainerCanInjectSimpleVariableToBinding(): void
//    {
//        $this->container->bind(ContainerInjectVariableFixture::class);
//
//        $this->container->when(ContainerInjectVariableFixture::class)
//            ->needs('$something')
//            ->give(100);
//        $instance = $this->container->make(ContainerInjectVariableFixture::class);
//
//        static::assertEquals(100, $instance->something);
//    }
//
//    public function testContainerWhenNeedsGiveToThrowException(): void
//    {
//        $this->expectException(\Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException::class);
//        $this->expectExceptionMessage('Parameter [something] cannot be injected in [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerInjectVariableFixture].');
//
//        $this->container->when(ContainerInjectVariableFixture::class . '@set')
//            ->needs(ContainerConcreteFixture::class)
//            ->needs('$something')
//            ->give(100);
//        $instance = $this->container->make(ContainerInjectVariableFixture::class);
//
//        static::assertEquals(100, $instance->something);
//    }
//
//    public function testContainerCantInjectObjectIsNotResolvable(): void
//    {
//        $this->expectException(\Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException::class);
//        $this->expectExceptionMessage('[Viserio\\Component\\Container\\Tests\\ContainerTestNotResolvable] is not resolvable.');
//
//        $this->container->when('Viserio\Component\Container\Tests\ContainerTestNotResolvable')
//            ->needs(ContainerConcreteFixture::class)
//            ->give(100);
//
//        $instance = $this->container->make(ContainerInjectVariableFixture::class);
//
//        static::assertEquals(100, $instance->something);
//    }
//
//    public function testContainerCantInjectObjectWithoutConstructor(): void
//    {
//        $this->expectException(\Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException::class);
//        $this->expectExceptionMessage('[Viserio\\Component\\Container\\Tests\\Fixture\\ContainerTestNoConstructor] must have a constructor.');
//
//        $this->container->when(ContainerTestNoConstructor::class)
//            ->needs(ContainerConcreteFixture::class)
//            ->give(100);
//
//        $instance = $this->container->make(ContainerInjectVariableFixture::class);
//
//        static::assertEquals(100, $instance->something);
//    }
//
//    public function testContainerCanInjectDifferentImplementationsDependingOnContext(): void
//    {
//        $this->container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
//
//        $this->container->when(ContainerTestContextInjectOneFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(ContainerImplementationFixture::class);
//
//        $this->container->when(ContainerTestContextInjectTwoFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(ContainerImplementationTwoFixture::class);
//
//        $one = $this->container->make(ContainerTestContextInjectOneFixture::class);
//        $two = $this->container->make(ContainerTestContextInjectTwoFixture::class);
//
//        static::assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
//        static::assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);
//
//        // Test With Closures
//        $this->container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
//        $this->container->when(ContainerTestContextInjectOneFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(ContainerImplementationFixture::class);
//        $this->container->when(ContainerTestContextInjectTwoFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(function ($container) {
//                return $this->container->make(ContainerImplementationTwoFixture::class);
//            });
//
//        $one = $this->container->make(ContainerTestContextInjectOneFixture::class);
//        $two = $this->container->make(ContainerTestContextInjectTwoFixture::class);
//
//        static::assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
//        static::assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);
//    }
//
//    public function testContextualBindingWorksForExistingInstancedBindings(): void
//    {
//        $this->container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());
//
//        $this->container->when(ContainerTestContextInjectOneFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(ContainerImplementationTwoFixture::class);
//
//        static::assertInstanceOf(
//            ContainerImplementationTwoFixture::class,
//            $this->container->make(ContainerTestContextInjectOneFixture::class)->impl
//        );
//    }
//
//    public function testContextualBindingWorksForNewlyInstancedBindings(): void
//    {
//        $this->container->when(ContainerTestContextInjectOneFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(ContainerTestContextInjectTwoFixture::class);
//
//        $this->container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());
//
//        static::assertInstanceOf(
//            ContainerTestContextInjectTwoFixture::class,
//            $this->container->make(ContainerTestContextInjectOneFixture::class)->impl
//        );
//    }
//
//    public function testContextualBindingWorksOnExistingAliasedInstances(): void
//    {
//        $this->container->instance('stub', new ContainerImplementationFixture());
//        $this->container->alias('stub', ContainerContractFixtureInterface::class);
//
//        $this->container->when(ContainerTestContextInjectOneFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(ContainerTestContextInjectTwoFixture::class);
//
//        static::assertInstanceOf(
//            ContainerTestContextInjectTwoFixture::class,
//            $this->container->make(ContainerTestContextInjectOneFixture::class)->impl
//        );
//    }
//
//    public function testContextualBindingWorksOnNewAliasedInstances(): void
//    {
//        $this->container->when(ContainerTestContextInjectOneFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(ContainerTestContextInjectTwoFixture::class);
//
//        $this->container->instance('stub', new ContainerImplementationFixture());
//        $this->container->alias('stub', ContainerContractFixtureInterface::class);
//
//        static::assertInstanceOf(
//            ContainerTestContextInjectTwoFixture::class,
//            $this->container->make(ContainerTestContextInjectOneFixture::class)->impl
//        );
//    }
//
//    public function testContextualBindingWorksOnNewAliasedBindings(): void
//    {
//        $this->container->when(ContainerTestContextInjectOneFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(ContainerTestContextInjectTwoFixture::class);
//
//        $this->container->bind('stub', ContainerImplementationFixture::class);
//        $this->container->alias('stub', ContainerContractFixtureInterface::class);
//
//        static::assertInstanceOf(
//            ContainerTestContextInjectTwoFixture::class,
//            $this->container->make(ContainerTestContextInjectOneFixture::class)->impl
//        );
//    }
//
//    public function testContextualBindingDoesntOverrideNonContextualResolution(): void
//    {
//        $this->container->instance('stub', new ContainerImplementationFixture());
//        $this->container->alias('stub', ContainerContractFixtureInterface::class);
//
//        $this->container->when(ContainerTestContextInjectTwoFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give(ContainerTestContextInjectInstantiationsFixture::class);
//
//        static::assertInstanceOf(
//            ContainerTestContextInjectInstantiationsFixture::class,
//            $this->container->make(ContainerTestContextInjectTwoFixture::class)->impl
//        );
//
//        static::assertInstanceOf(
//            ContainerImplementationFixture::class,
//            $this->container->make(ContainerTestContextInjectOneFixture::class)->impl
//        );
//    }
//
//    public function testContextuallyBoundInstancesAreNotUnnecessarilyRecreated(): void
//    {
//        ContainerTestContextInjectInstantiationsFixture::$instantiations = 0;
//
//        $this->container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());
//        $this->container->instance('ContainerTestContextInjectInstantiationsFixture', new ContainerTestContextInjectInstantiationsFixture());
//
//        static::assertEquals(1, ContainerTestContextInjectInstantiationsFixture::$instantiations);
//
//        $this->container->when(ContainerTestContextInjectOneFixture::class)
//            ->needs(ContainerContractFixtureInterface::class)
//            ->give('ContainerTestContextInjectInstantiationsFixture');
//
//        $this->container->make(ContainerTestContextInjectOneFixture::class);
//        $this->container->make(ContainerTestContextInjectOneFixture::class);
//        $this->container->make(ContainerTestContextInjectOneFixture::class);
//        $this->container->make(ContainerTestContextInjectOneFixture::class);
//
//        static::assertEquals(1, ContainerTestContextInjectInstantiationsFixture::$instantiations);
//    }
//
//    public function testContextualBindingNotWorksOnBoundAlias(): void
//    {
//        $this->expectException(\Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException::class);
//        $this->expectExceptionMessage('Parameter [stub] cannot be injected in [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerTestContextInjectOneFixture].');
//
//        $this->container->alias(ContainerContractFixtureInterface::class, 'stub');
//        $this->container->bind('stub', ContainerImplementationFixture::class);
//
//        $this->container->when(ContainerTestContextInjectOneFixture::class)->needs('stub')->give(ContainerImplementationTwoFixture::class);
//
//        $this->container->get(ContainerTestContextInjectOneFixture::class);
//    }
//}
