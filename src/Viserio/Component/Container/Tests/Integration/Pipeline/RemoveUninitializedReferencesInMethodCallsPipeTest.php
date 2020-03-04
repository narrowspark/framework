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
use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\RemoveUninitializedReferencesInMethodCallsPipe;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\RemoveUninitializedReferencesInMethodCallsPipe
 *
 * @small
 */
final class RemoveUninitializedReferencesInMethodCallsPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('foo', stdClass::class);
        $container->singleton(stdClass::class)
            ->addMethodCall('foo', [new ReferenceDefinition('bar', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('foo', [new ReferenceDefinition('foo', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('foo', [new ReferenceDefinition('bar', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE), new ReferenceDefinition('bar2', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)]);

        $this->process($container);

        self::assertSame('Viserio\Component\Container\Pipeline\RemoveUninitializedReferencesInMethodCallsPipe: The method call [foo] for definition [stdClass] was removed because needed service [\'bar\'] was not found.', $container->getLogs()[0]);
        self::assertSame('Viserio\Component\Container\Pipeline\RemoveUninitializedReferencesInMethodCallsPipe: The method call [foo] for definition [stdClass] was removed because needed services [\'bar\', \'bar2\'] were not found.', $container->getLogs()[1]);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(stdClass::class);

        self::assertCount(1, $definition->getMethodCalls());
    }

    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new RemoveUninitializedReferencesInMethodCallsPipe();

        $pipe->process($container);
    }
}
