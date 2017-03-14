<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Resolvers;

use stdClass;
use Viserio\Bridge\Doctrine\ORM\Resolvers\EntityListenerResolver;
use Interop\Container\ContainerInterface;
use Doctrine\ORM\Mapping\EntityListenerResolver as ResolverContract;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;

class EntityListenerResolverTest extends MockeryTestCase
{
    public function testImplementsDoctrineInterface()
    {
        $this->assertInstanceOf(
            ResolverContract::class,
            new EntityListenerResolver($this->mock(ContainerInterface::class))
        );
    }

    public function testResolvesFromContainer()
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

    public function testHoldsReferenceAfterResolve()
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

    public function testClearsHeldReference()
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

    public function testClearsAllHeldReferences()
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

    public function testAllowsDirectlyRegisteringListeners()
    {
        $object = new stdClass();

        $container = $this->mock(ContainerInterface::class);
        $resolver  = new EntityListenerResolver($container);
        $resolver->register($object);

        $resolvedObject = $resolver->resolve(get_class($object));

        $this->assertSame($object, $resolvedObject, "Resolver should not use container when directly registering");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage An object was expected, but got "string".
     */
    public function testDoesNotAllowRegisteringNonObjects()
    {
        $container = $this->mock(ContainerInterface::class);
        $resolver  = new EntityListenerResolver($container);
        $resolver->register('foo');
    }
}
