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
use Psr\Container\ContainerInterface;
use stdClass;
use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
use Viserio\Contract\Container\Definition\TagAwareDefinition;

/**
 * @internal
 *
 * @small
 */
final class ResolvePreloadPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $container->bind('foo', stdClass::class)
            ->addArgument(new IteratorArgument([new ReferenceDefinition('lazy')]))
            ->addArgument(new ReferenceDefinition(ContainerInterface::class))
            ->addArgument((new ObjectDefinition('bart', stdClass::class, 1))->addArgument(new ReferenceDefinition('bar')))
            ->addArgument(new ReferenceDefinition('baz', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE))
            ->addArgument(new ReferenceDefinition('missing'))
            ->addTag('container.preload');

        $container->bind('lazy');
        $container->bind('bar', stdClass::class)
            ->addArgument(new ReferenceDefinition('buz'))
            ->addArgument(new ReferenceDefinition('deprec_ref_notag'));

        $container->bind('baz', stdClass::class)
            ->addArgument(new ReferenceDefinition('lazy'))
            ->addArgument(new ReferenceDefinition('lazy'));

        $container->bind('buz');
        $container->bind('deprec_with_tag')->setDeprecated()->addTag('container.preload');
        $container->bind('deprec_ref_notag')->setDeprecated();

        $this->process($container);

        /** @var TagAwareDefinition $definition */
        $definition = $container->getDefinition('bar');
        /** @var TagAwareDefinition $definition2 */
        $definition2 = $container->getDefinition('buz');
        /** @var TagAwareDefinition $definition3 */
        $definition3 = $container->getDefinition('lazy');
        /** @var TagAwareDefinition $definition4 */
        $definition4 = $container->getDefinition('baz');
        /** @var TagAwareDefinition $definition5 */
        $definition5 = $container->getDefinition('deprec_with_tag');
        /** @var TagAwareDefinition $definition6 */
        $definition6 = $container->getDefinition('deprec_ref_notag');

        self::assertTrue($definition->hasTag('container.preload'));
        self::assertTrue($definition2->hasTag('container.preload'));
        self::assertFalse($definition3->hasTag('container.preload'));
        self::assertFalse($definition4->hasTag('container.preload'));
        self::assertFalse($definition5->hasTag('container.preload'));
        self::assertFalse($definition6->hasTag('container.preload'));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new ResolvePreloadPipe();

        $pipe->process($container);
    }
}
