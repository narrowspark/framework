<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use Throwable;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tests\Fixture\ContainerCircularReferenceStubA;
use Viserio\Component\Container\Tests\Fixture\ContainerCircularReferenceStubD;
use Viserio\Component\Contract\Container\Exception\CyclicDependencyException;

/**
 * @internal
 */
final class CircularDependencyTest extends BaseContainerTest
{
    /**
     * Tests if instantiation unlock works: we should be able to get the same entry twice.
     *
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     * @doesNotPerformAssertions
     */
    public function testCanGetTheSameEntryTwice(ContainerBuilder $builder): void
    {
        $builder->bind(\stdClass::class);

        $container = $builder->build();

        try {
            $container->get(\stdClass::class);
            $container->get(\stdClass::class);
        } catch (Throwable $exception) {
            static::fail(__METHOD__ . ': ' . $exception->getMessage());
        }
    }

    /**
     * Since the dependency is ( D -> F -> E -> D ), the exception
     * message should state that the issue starts in class D.
     *
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testCircularReferenceCheck(ContainerBuilder $builder): void
    {
        $this->expectException(CyclicDependencyException::class);
        $this->expectExceptionMessage('Circular reference found while resolving [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerCircularReferenceStubD].');

        $builder->bind(ContainerCircularReferenceStubD::class);

        $builder->build()->get(ContainerCircularReferenceStubD::class);
    }

    /**
     * Since the dependency is ( A -> B -> C -> B ), the exception
     * message should state that the issue starts in class B.
     *
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testCircularReferenceCheckDetectCycleStartLocation(ContainerBuilder $builder): void
    {
        $this->expectException(CyclicDependencyException::class);
        $this->expectExceptionMessage('Circular reference found while resolving [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerCircularReferenceStubB].');

        $builder->bind(ContainerCircularReferenceStubA::class);

        $builder->build()->get(ContainerCircularReferenceStubA::class);
    }
}
