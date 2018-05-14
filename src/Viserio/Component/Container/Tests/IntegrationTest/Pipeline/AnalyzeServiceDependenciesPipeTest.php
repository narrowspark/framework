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
use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\AnalyzeServiceDependenciesPipe;
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\Tests\Fixture\Pipeline\A;
use Viserio\Component\Container\Tests\Fixture\Pipeline\B;
use Viserio\Component\Container\Tests\Fixture\Pipeline\C;
use Viserio\Component\Container\Tests\Fixture\Pipeline\D;

/**
 * @internal
 *
 * @small
 */
final class AnalyzeServiceDependenciesPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $container->singleton('a', A::class)
            ->addArgument($ref1 = new ReferenceDefinition('b'));
        $container->singleton('b', B::class)
            ->addMethodCall('setA', [$ref2 = new ReferenceDefinition('a')]);
        $container->singleton('c', C::class)
            ->addArgument($ref3 = new ReferenceDefinition('a'))
            ->addArgument($ref4 = new ReferenceDefinition('b'));
        $container->singleton('d', D::class)
            ->setProperty('foo', $ref5 = new ReferenceDefinition('b'));

        $this->process($container);

        $graph = $container->getServiceReferenceGraph();
        $edges = $graph->getNode('b')->getInEdges();

        self::assertCount(3, $edges);

        self::assertSame($ref1, $edges[0]->getValue());
        self::assertSame($ref4, $edges[1]->getValue());
        self::assertSame($ref5, $edges[2]->getValue());
    }

    public function testProcessMarksEdgesLazyWhenReferencedServiceIsLazy(): void
    {
        $container = new ContainerBuilder();

        $container->singleton('a', A::class)
            ->setLazy(true)
            ->addArgument($ref1 = new ReferenceDefinition('b'));
        $container->singleton('b', B::class)
            ->addArgument($ref2 = new ReferenceDefinition('a'));

        $this->process($container);

        $graph = $container->getServiceReferenceGraph();

        self::assertCount(1, $graph->getNode('b')->getInEdges());
        self::assertCount(1, $edges = $graph->getNode('a')->getInEdges());

        self::assertSame($ref2, $edges[0]->getValue());
        self::assertTrue($edges[0]->isLazy());
    }

    public function testProcessMarksEdgesLazyWhenReferencedFromIteratorArgument(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('a', A::class);
        $container->singleton('b', B::class);

        $container->singleton('c', C::class)
            ->addArgument($ref1 = new ReferenceDefinition('a'))
            ->addArgument(new IteratorArgument([$ref2 = new ReferenceDefinition('b')]));

        $this->process($container);

        $graph = $container->getServiceReferenceGraph();

        self::assertCount(1, $graph->getNode('a')->getInEdges());
        self::assertCount(1, $graph->getNode('b')->getInEdges());
        self::assertCount(2, $edges = $graph->getNode('c')->getOutEdges());

        self::assertSame($ref1, $edges[0]->getValue());
        self::assertFalse($edges[0]->isLazy());
        self::assertSame($ref2, $edges[1]->getValue());
        self::assertTrue($edges[1]->isLazy());
    }

    public function testProcessDetectsReferencesFromInlinedDefinitions(): void
    {
        $container = new ContainerBuilder();

        $container->bind(A::class);
        $container->bind(B::class);

        (new AutowirePipe())->process($container);

        $this->process($container, true);

        $graph = $container->getServiceReferenceGraph();
        $refs = $graph->getNode(B::class)->getInEdges();

        self::assertCount(1, $refs);

        $ref1 = $refs[0]->getValue();

        self::assertSame(B::class, $ref1->getName());
    }

    public function testProcessWithFactory(): void
    {
        $container = new ContainerBuilder();

        $container->bind(A::class);
        $container->bind('setA', B::class . '@setA');

        (new AutowirePipe())->process($container);

        $this->process($container);

        $graph = $container->getServiceReferenceGraph();
        $edges = $graph->getNode(A::class)->getInEdges();

        self::assertCount(1, $edges);

        $edge1 = $edges[0]->getValue();

        self::assertSame(A::class, $edge1->getName());
    }

    public function testProcessDetectsReferencesFromIteratorArguments(): void
    {
        $container = new ContainerBuilder();

        $container->singleton('a');

        $container->singleton('b')
            ->addArgument(new IteratorArgument([$ref = new ReferenceDefinition('a')]));

        $this->process($container);

        $graph = $container->getServiceReferenceGraph();

        self::assertCount(1, $refs = $graph->getNode('a')->getInEdges());
        self::assertSame($ref, $refs[0]->getValue());
    }

    public function testProcessDetectsReferencesFromInlinedFactoryDefinitions(): void
    {
        $container = new ContainerBuilder();

        $container->singleton('a');

        $container->singleton('b')
            ->addArgument(new FactoryDefinition('a', [new ReferenceDefinition('a'), 'a'], 1));

        $this->process($container);

        $graph = $container->getServiceReferenceGraph();

        self::assertTrue($graph->hasNode('a'));
        self::assertCount(1, $refs = $graph->getNode('a')->getInEdges());
    }

    public function testProcessDoesNotSaveDuplicateReferences(): void
    {
        $container = new ContainerBuilder();

        $container->bind(A::class);
        $container->bind(D::class);

        (new AutowirePipe())->process($container);

        $this->process($container);

        $graph = $container->getServiceReferenceGraph();

        self::assertCount(2, $graph->getNode(A::class)->getInEdges());
    }

    public function testProcessDetectsFactoryReferences(): void
    {
        $container = new ContainerBuilder();

        $container->singleton('foo', [\stdClass::class, 'getInstance']);
        $container->singleton('bar', [new ReferenceDefinition('foo'), 'getInstance']);

        $this->process($container);

        $graph = $container->getServiceReferenceGraph();

        self::assertTrue($graph->hasNode('foo'));
        self::assertCount(1, $graph->getNode('foo')->getInEdges());
    }

    /**
     * @param ContainerBuilder $container
     * @param bool             $onlyConstructorArguments
     */
    private function process(ContainerBuilder $container, bool $onlyConstructorArguments = false): void
    {
        $pipes = [
            new AnalyzeServiceDependenciesPipe($onlyConstructorArguments),
        ];

        foreach ($pipes as $pipe) {
            $pipe->process($container);
        }
    }
}
