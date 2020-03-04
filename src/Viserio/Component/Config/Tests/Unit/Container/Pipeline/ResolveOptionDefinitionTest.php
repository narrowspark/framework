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
 * @coversNothing
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

    /**
     *
     * public function testDimensionsProcess(): void
     * {
     */

    private function process(ContainerBuilder $container): void
    {
        $pipe = new ResolveConfigDefinitionPipe();

        $pipe->process($container);
    }
}
