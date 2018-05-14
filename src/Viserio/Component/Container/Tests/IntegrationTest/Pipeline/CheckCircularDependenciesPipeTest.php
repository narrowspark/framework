<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Container\Tests\IntegrationTest\Pipeline;

use PHPUnit\Framework\TestCase;
use Throwable;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\AnalyzeServiceDependenciesPipe;
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\Pipeline\CheckCircularReferencesPipe;
use Viserio\Component\Container\Tests\Fixture\Circular\FactoryReferenceStub;
use Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubA;
use Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB;
use Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubC;
use Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubD;
use Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubE;
use Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubF;
use Viserio\Contract\Container\Exception\CircularDependencyException;

/**
 * @internal
 *
 * @small
 */
final class CheckCircularDependenciesPipeTest extends TestCase
{
    /**
     * Tests if instantiation unlock works: we should be able to get the same entry twice.
     *
     * @doesNotPerformAssertions
     */
    public function testCanGetTheSameEntryTwice(): void
    {
        $container = new ContainerBuilder();
        $container->bind(\stdClass::class);

        $this->process($container);

        try {
            $container->getDefinition(\stdClass::class);
            $container->getDefinition(\stdClass::class);
        } catch (Throwable $exception) {
            self::fail(__METHOD__ . ': ' . $exception->getMessage());
        }
    }

    /**
     * Since the dependency is ( D -> F -> E -> D ), the exception
     * message should state that the issue starts in class D.
     */
    public function testProcessWithCircularD(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular reference detected for service [Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubD]; path: [Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubD -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubE -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubF -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubD].');

        $container = new ContainerBuilder();
        $container->bind(ReferenceStubD::class);
        $container->bind(ReferenceStubF::class);
        $container->bind(ReferenceStubE::class);

        $this->process($container);
    }

    /**
     * Since the dependency is ( D -> F -> E -> D ), the exception
     * message should state that the issue starts in class D.
     */
    public function testProcessWithCircularDAndAutoGeneratedClasses(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular reference detected for service [Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubD]; path: [Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubD -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubE -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubF -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubD].');

        $container = new ContainerBuilder();
        $container->bind(ReferenceStubD::class);

        $this->process($container);
    }

    /**
     * Since the dependency is ( A -> B -> C -> B ), the exception
     * message should state that the issue starts in class B.
     */
    public function testProcessDetectCycleStartLocation(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular reference detected for service [Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB]; path: [Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubA -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubC -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB].');

        $container = new ContainerBuilder();
        $container->bind(ReferenceStubA::class);
        $container->bind(ReferenceStubB::class);
        $container->bind(ReferenceStubC::class);

        $this->process($container);
    }

    /**
     * Since the dependency is ( A -> B -> C -> B ), the exception
     * message should state that the issue starts in class B.
     */
    public function testProcessDetectCycleStartLocationAndAutoGeneratedClassesb(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular reference detected for service [Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB]; path: [Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubA -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubC -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB].');

        $container = new ContainerBuilder();
        $container->bind(ReferenceStubA::class);

        $this->process($container);
    }

    public function testProcessIgnoresLazyServices(): void
    {
        $container = new ContainerBuilder();
        $container->bind(ReferenceStubD::class)
            ->setLazy(true);

        $this->process($container);

        // just make sure that a lazily loaded services does not trigger a CircularReferenceException
        $this->addToAssertionCount(1);
    }

    public function testProcessDetectsIndirectCircularReferenceWithFactory(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular reference detected for service [Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB]; path: [factory -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubA -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubC -> Viserio\Component\Container\Tests\Fixture\Circular\ReferenceStubB].');

        $container = new ContainerBuilder();
        $container->bind('factory', [FactoryReferenceStub::class, 'set']);

        $this->process($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipes = [
            new AutowirePipe(),
            new AnalyzeServiceDependenciesPipe(),
            new CheckCircularReferencesPipe(),
        ];

        foreach ($pipes as $pipe) {
            $pipe->process($container);
        }
    }
}
