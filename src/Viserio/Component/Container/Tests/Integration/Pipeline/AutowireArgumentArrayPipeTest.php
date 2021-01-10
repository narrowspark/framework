<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Container\Tests\Integration\Pipeline;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Pipeline\AutowireArgumentArrayPipe;
use Viserio\Component\Container\Tests\Fixture\ArrayAutowire\A;
use Viserio\Component\Container\Tests\Fixture\ArrayAutowire\B;
use Viserio\Component\Container\Tests\Fixture\ArrayAutowire\Bar;
use Viserio\Component\Container\Tests\Fixture\ArrayAutowire\Foo;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\AutowireArgumentArrayPipe
 *
 * @small
 */
final class AutowireArgumentArrayPipeTest extends TestCase
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

    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new AutowireArgumentArrayPipe();

        $pipe->process($container);
    }
}
