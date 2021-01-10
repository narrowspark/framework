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
use Viserio\Component\Container\Pipeline\ExtendedDefinitionPipe;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\ExtendedDefinitionPipe
 *
 * @small
 */
final class ExtendedDefinitionValuePipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('array', [
            'foo' => 'bar',
        ]);
        $container->extend('array', static function (DefinitionContract $definition) {
            $array = $definition->getValue();

            $array['yeah'] = 'merge';

            return $definition->setValue($array);
        });

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ArrayDefinition $definition */
        $definition = $container->getDefinition('array');

        self::assertSame(
            [
                'foo' => 'bar',
                'yeah' => 'merge',
            ],
            $definition->getValue()
        );
    }

    public function testProcessWithTwoExtends(): void
    {
        $container = new ContainerBuilder();
        $container->bind('foo', 'bar');
        $container->bind('be', 'be_');
        $container->extend('foo', static function (DefinitionContract $definition) {
            return $definition->setValue('DIFFERENT_' . $definition->getValue());
        });
        $container->extend('foo', static function (DefinitionContract $definition, ContainerBuilder $container) {
            return $definition->setValue($container->getDefinition('be')->getValue() . $definition->getValue());
        });

        self::assertCount(2, $container->getExtenders('foo'));

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ParameterDefinition $definition */
        $definition = $container->getDefinition('foo');

        self::assertSame('be_DIFFERENT_bar', $definition->getValue());
    }

    public function testExtendBeforeDefinitionBinding(): void
    {
        $container = new ContainerBuilder();
        $container->extend('array', static function (DefinitionContract $definition) {
            $array = $definition->getValue();

            $array['yeah'] = 'merge';

            return $definition->setValue($array);
        });
        $container->singleton('array', [
            'foo' => 'bar',
        ]);

        self::assertCount(1, $container->getExtenders('array'));

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ArrayDefinition $definition */
        $definition = $container->getDefinition('array');

        self::assertSame(
            [
                'foo' => 'bar',
                'yeah' => 'merge',
            ],
            $definition->getValue()
        );
    }

    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new ExtendedDefinitionPipe();

        $pipe->process($container);
    }
}
