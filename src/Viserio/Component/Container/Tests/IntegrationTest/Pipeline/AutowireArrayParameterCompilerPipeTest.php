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
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Pipeline\AutowireArrayParameterCompilerPipe;
use Viserio\Component\Container\Tests\Fixture\ArrayAutowire\A;
use Viserio\Component\Container\Tests\Fixture\ArrayAutowire\B;
use Viserio\Component\Container\Tests\Fixture\ArrayAutowire\Bar;
use Viserio\Component\Container\Tests\Fixture\ArrayAutowire\Foo;

/**
 * @internal
 *
 * @small
 */
final class AutowireArrayParameterCompilerPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->singleton(Foo::class);
        $container->singleton(A::class);
        $container->singleton(B::class);

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition(Foo::class);
        $servicesArguments = $definition->getArguments()['$services'];

        self::assertCount(2, $servicesArguments);
        self::assertSame(A::class, $servicesArguments[0]->getName());
        self::assertSame(B::class, $servicesArguments[1]->getName());
    }

    public function testProcessWithUnknownServices(): void
    {
        $container = new ContainerBuilder();
        $container->singleton(Bar::class);

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition(Bar::class);

        self::assertCount(0, $definition->getArguments());
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new AutowireArrayParameterCompilerPipe();

        $pipe->process($container);
    }
}
