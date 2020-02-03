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

namespace Viserio\Component\Container\Tests\Integration\Pipeline;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\Pipeline\RemoveUnusedDefinitionsPipe;
use Viserio\Component\Container\Tests\Fixture\Autowire\VariadicClass;
use Viserio\Component\Container\Tests\Fixture\EmptyClass;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\AutowirePipe
 * @covers \Viserio\Component\Container\Pipeline\RemoveUnusedDefinitionsPipe
 *
 * @small
 */
final class RemoveUnusedDefinitionsPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->bind('bar', stdClass::class);
        $container->bind(EmptyClass::class);
        $container->bind(VariadicClass::class)
            ->setPublic(true);

        $this->process($container);

        self::assertFalse($container->hasDefinition('bar'));
        self::assertTrue($container->hasDefinition(EmptyClass::class));
        self::assertTrue($container->hasDefinition(VariadicClass::class));
    }

    public function testProcessRemovesUnusedDefinitionsRecursively(): void
    {
        $container = new ContainerBuilder();
        $container->bind(EmptyClass::class);
        $container->bind(VariadicClass::class);

        $this->process($container);

        self::assertFalse($container->hasDefinition(EmptyClass::class));
        self::assertFalse($container->hasDefinition(VariadicClass::class));
    }

    public function testProcessWontRemovePrivateFactory(): void
    {
        $container = new ContainerBuilder();
        $container->bind('foo', [Remove::class, 'getInstance']);
        $container->bind('bar', [new ReferenceDefinition('foo'), 'getInstance']);
        $container->bind('foobar', stdClass::class)
            ->addArgument(new ReferenceDefinition('bar'))
            ->setPublic(true);

        $this->process($container);

        self::assertTrue($container->hasDefinition('foo'));
        self::assertTrue($container->hasDefinition('bar'));
        self::assertTrue($container->hasDefinition('foobar'));
    }

    public function testProcessDoesNotErrorOnServicesThatDoNotHaveDefinitions(): void
    {
        $container = new ContainerBuilder();
        $container
            ->bind('defined', stdClass::class)
            ->addArgument(new ReferenceDefinition('not.defined'))
            ->setPublic(true);

        $this->process($container);

        self::assertFalse($container->hasDefinition('not.defined'));
    }

    /**
     * @param \Viserio\Contract\Container\ContainerBuilder $container
     */
    private function process(ContainerBuilderContract $container): void
    {
        $pipes = [
            new AutowirePipe(),
            new RemoveUnusedDefinitionsPipe(),
        ];

        foreach ($pipes as $pipe) {
            $pipe->process($container);
        }
    }
}

class Remove
{
    public function getInstance(): void
    {
    }
}
