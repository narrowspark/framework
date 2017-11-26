<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Resolvers;

use Doctrine\ORM\Mapping\EntityListenerResolver as ResolverContract;
use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver;

class EntityListenerResolverTest extends MockeryTestCase
{
    public function testImplementsDoctrineInterface(): void
    {
        $this->assertInstanceOf(
            ResolverContract::class,
            new EntityListenerResolver($this->mock(ContainerInterface::class))
        );
    }

    public function testResolvesFromContainer(): void
    {
        $object = new stdClass();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with('class')
            ->andReturn($object);
        $resolver = new EntityListenerResolver($container);

        $resolvedObject = $resolver->resolve('class');

        $this->assertSame($object, $resolvedObject, 'Resolver should retrieve the object from the container.');
    }

    public function testHoldsReferenceAfterResolve(): void
    {
        $object        = new stdClass();
        $anotherObject = new stdClass();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with('class')
            ->once()
            ->andReturn($object, $anotherObject);

        $resolver = new EntityListenerResolver($container);

        $resolvedObject      = $resolver->resolve('class');
        $resolvedObjectAgain = $resolver->resolve('class');

        $this->assertSame($object, $resolvedObject, 'Resolver should retrieve the object from the container.');
        $this->assertSame($object, $resolvedObjectAgain, 'Resolver should retrieve the object from its own reference.');
    }

    public function testClearsHeldReference(): void
    {
        $object        = new stdClass();
        $anotherObject = new stdClass();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with('class')
            ->twice()
            ->andReturn($object, $anotherObject);

        $resolver = new EntityListenerResolver($container);
        $resolver->resolve('class');
        $resolver->clear('class');

        $resolvedObjectAgain = $resolver->resolve('class');

        $this->assertSame($anotherObject, $resolvedObjectAgain, 'Resolver should got back to container after clear');
    }

    public function testClearsAllHeldReferences(): void
    {
        $object           = new stdClass();
        $anotherObject    = new stdClass();
        $oneMoreObject    = new stdClass();
        $yetAnotherObject = new stdClass();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with('class')
            ->twice()
            ->andReturn($object, $anotherObject);
        $container->shouldReceive('get')
            ->with('class2')
            ->twice()
            ->andReturn($oneMoreObject, $yetAnotherObject);

        $resolver = new EntityListenerResolver($container);
        $resolver->resolve('class');
        $resolver->resolve('class2');
        $resolver->clear();

        $resolvedAnotherObject    = $resolver->resolve('class');
        $resolvedYetAnotherObject = $resolver->resolve('class2');

        $this->assertSame($anotherObject, $resolvedAnotherObject, 'Resolver should retrieve the object from the container.');
        $this->assertSame($yetAnotherObject, $resolvedYetAnotherObject, 'Resolver should retrieve the object from the container.');
    }

    public function testAllowsDirectlyRegisteringListeners(): void
    {
        $object = new stdClass();

        $container = $this->mock(ContainerInterface::class);
        $resolver  = new EntityListenerResolver($container);
        $resolver->register($object);

        $resolvedObject = $resolver->resolve(get_class($object));

        $this->assertSame($object, $resolvedObject, 'Resolver should not use container when directly registering');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage An object was expected, but got "string".
     */
    public function testDoesNotAllowRegisteringNonObjects(): void
    {
        $container = $this->mock(ContainerInterface::class);
        $resolver  = new EntityListenerResolver($container);
        $resolver->register('foo');
    }
}
