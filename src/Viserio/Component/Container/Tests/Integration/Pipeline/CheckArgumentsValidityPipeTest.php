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
use Viserio\Component\Container\Pipeline\CheckArgumentsValidityPipe;
use Viserio\Component\Container\Tests\Fixture\Invoke\InvokeWithConstructorParameterClass;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\CheckArgumentsValidityPipe
 *
 * @small
 */
final class CheckArgumentsValidityPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $definition = $container->bind('foo', new stdClass());
        $definition->setArguments([null, 1, 'a']);
        $definition->setMethodCalls([
            ['bar', ['a', 'b']],
            ['baz', ['c', 'd']],
        ]);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');

        self::assertEquals([null, 1, 'a'], $definition->getArguments());
        self::assertEquals([
            ['bar', ['a', 'b'], false],
            ['baz', ['c', 'd'], false],
        ], $definition->getMethodCalls());
    }

    /**
     * @dataProvider provideExceptionCases
     *
     * @param array  $arguments
     * @param array  $methodCalls
     * @param string $message
     */
    public function testException(array $arguments, array $methodCalls, string $message): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        $container = new ContainerBuilder();

        $definition = $container->bind('foo', new stdClass());
        $definition->setArguments($arguments);
        $definition->setMethodCalls($methodCalls);

        $this->process($container);
    }

    public function testExceptionWithFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid constructor argument for service [foo]: integer expected but found string [a]. Check your service definition.');

        $container = new ContainerBuilder();

        $definition = $container->bind('foo', [InvokeWithConstructorParameterClass::class, '__invoke']);
        $definition->setClassArguments(['a' => null]);

        $this->process($container);
    }

    public static function provideExceptionCases(): iterable
    {
        return [
            [[null, 'a' => 'a'], [], 'Invalid constructor argument for service [foo]: integer expected but found string [a]. Check your service definition.'],
            [[1 => 1], [], 'Invalid constructor argument [2] for service [foo]: argument [1] must be defined before. Check your service definition.'],
            [[], [['baz', [null, 'a' => 'a']]], 'Invalid argument for method call [baz] of service [foo]: integer expected but found string [a]. Check your service definition.'],
            [[], [['baz', [1 => 1]]], 'Invalid argument [2] for method call [baz] of service [foo]: argument [1] must be defined before. Check your service definition.'],
        ];
    }

    /**
     * @param \Viserio\Contract\Container\ContainerBuilder $container
     */
    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new CheckArgumentsValidityPipe();

        $pipe->process($container);
    }
}
