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
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\RemoveUninitializedReferencesInMethodCallsPipe;

/**
 * @internal
 *
 * @small
 */
final class RemoveUninitializedReferencesInMethodCallsPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('foo', \stdClass::class);
        $container->singleton(\stdClass::class)
            ->addMethodCall('foo', [new ReferenceDefinition('bar', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('foo', [new ReferenceDefinition('foo', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('foo', [new ReferenceDefinition('bar', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE), new ReferenceDefinition('bar2', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)]);

        $this->process($container);

        self::assertSame('Viserio\Component\Container\Pipeline\RemoveUninitializedReferencesInMethodCallsPipe: The method call [foo] for definition [stdClass] was removed because needed service [\'bar\'] was not found.', $container->getLogs()[0]);
        self::assertSame('Viserio\Component\Container\Pipeline\RemoveUninitializedReferencesInMethodCallsPipe: The method call [foo] for definition [stdClass] was removed because needed services [\'bar\', \'bar2\'] were not found.', $container->getLogs()[1]);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(\stdClass::class);

        self::assertCount(1, $definition->getMethodCalls());
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new RemoveUninitializedReferencesInMethodCallsPipe();

        $pipe->process($container);
    }
}
