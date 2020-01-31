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

namespace Viserio\Component\Config\Tests\Unit\Unit\Container\Pipeline;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Config\Container\Pipeline\ResolveConfigDefinitionPipe;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigConfiguration;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ObjectDefinition;

/**
 * @internal
 *
 * @small
 */
final class ResolveOptionDefinitionTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('config', [
            'doctrine' => [
                'connection' => [],
            ],
        ]);
        $container->singleton('foo', stdClass::class)
            ->addArgument(new ConfigDefinition(ConnectionComponentDefaultConfigConfiguration::class));

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');

        /** @var ObjectDefinition $objectDefinition */
        $objectDefinition = $definition->getArgument(0);

        $arguments = $objectDefinition->getArguments();

        self::assertSame(ConnectionComponentDefaultConfigConfiguration::getDefaultConfig(), $arguments[0]);
    }

//
//    public function testDimensionsProcess(): void
//    {
//        $container = new ContainerBuilder();
//        $container->bind('config', [
//            'doctrine' => [
//                'connection' => [
//                    'foo' => 'test',
//                ],
//            ],
//        ]);
//        $container->singleton('foo', stdClass::class)
//            ->addArgument(new DimensionsConfigDefinition(ConnectionComponentConfiguration::class));
//
//        $this->process($container);
//
//        /** @var ObjectDefinition $definition */
//        $definition = $container->getDefinition('foo');
//
//        self::assertSame(['foo' => 'test'], $definition->getArgument(0));
//    }
//
//    public function testProcessThrowException(): void
//    {
//        $this->expectException(NotFoundException::class);
//
//        $this->process(new ContainerBuilder());
//    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new ResolveConfigDefinitionPipe();

        $pipe->process($container);
    }
}
