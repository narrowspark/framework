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
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\ResolveFactoryClassPipe;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\FactoryDefinition;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\ResolveFactoryClassPipe
 *
 * @small
 */
final class ResolveFactoryClassPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->singleton(FactoryClass::class);
        $container->bind('foo', [FactoryClass::class, 'create']);

        $this->process($container);

        /** @var FactoryDefinition $definition */
        $definition = $container->getDefinition('foo');

        self::assertInstanceOf(ReferenceDefinition::class, $definition->getValue()[0]);
        self::assertSame('create', $definition->getValue()[1]);
    }

    public function testProcessWithObject(): void
    {
        $container = new ContainerBuilder();
        $container->singleton(FactoryClass::class);
        $container->bind('foo', [new FactoryClass(), 'create']);

        $this->process($container);

        /** @var FactoryDefinition $definition */
        $definition = $container->getDefinition('foo');

        self::assertInstanceOf(ReferenceDefinition::class, $definition->getValue()[0]);
        self::assertSame('create', $definition->getValue()[1]);
    }

    public function testProcessWithFactoryObjectAndNotFoundEntry(): void
    {
        $container = new ContainerBuilder();
        $container->bind('foo', [new FactoryClass(), 'create']);

        $this->process($container);

        /** @var FactoryDefinition $definition */
        $definition = $container->getDefinition('foo');

        self::assertInstanceOf(FactoryClass::class, $definition->getValue()[0]);
        self::assertSame('create', $definition->getValue()[1]);
    }

    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new ResolveFactoryClassPipe();

        $pipe->process($container);
    }
}
