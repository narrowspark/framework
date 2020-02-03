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
use Psr\Container\ContainerInterface;
use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\ResolveParameterPlaceHolderPipe;
use Viserio\Component\Container\Pipeline\ResolveUndefinedDefinitionPipe;
use Viserio\Component\Container\Tests\Fixture\FooClass;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\CircularParameterException;
use Viserio\Contract\Container\Exception\NotFoundException;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\ResolveParameterPlaceHolderPipe
 * @covers \Viserio\Component\Container\Pipeline\ResolveUndefinedDefinitionPipe
 *
 * @small
 */
final class ResolveParameterPlaceHolderPipeTest extends TestCase
{
    /**
     * @dataProvider provideProcessCases
     *
     * @param array $asserts
     * @param array $parameters
     * @param array $services
     */
    public function testProcess(array $asserts, array $parameters = [], array $services = []): void
    {
        $container = new ContainerBuilder();

        foreach ($parameters as $service => $value) {
            $container->setParameter($service, $value)
                ->setPublic(true);
        }

        foreach ($services as $service => $calls) {
            $definition = $container->bind($service, $calls[0])
                ->setPublic(true);

            if (isset($calls[1])) {
                foreach ($calls[1] as $callback) {
                    $callback($definition);
                }
            }
        }

        $this->process($container);
        (new ResolveUndefinedDefinitionPipe())->process($container);

        foreach ($asserts as $key => $value) {
            [$method, $expected] = $value;

            if (\count($services) !== 0) {
                /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
                $definition = $container->getDefinition($key);
            } else {
                /** @var \Viserio\Component\Container\Definition\ParameterDefinition $definition */
                $definition = $container->getParameter($key);
            }

            self::assertSame($expected, $definition->{$method}());
        }
    }

    public static function provideProcessCases(): iterable
    {
        return [
            [
                [
                    'test' => ['getValue', 'bar'],
                ],
                ['test' => 'bar'],
            ],
            [
                [

                    'foo' => ['getValue', FooClass::class],
                ],
                [
                    'baz' => FooClass::class,
                ],
                [
                    'foo' => ['{baz}'],
                ],
            ],
            [
                [
                    'path' => ['getValue', '/private/tmp/logs/app.log'],
                ],
                [
                    'tmp' => '/private/tmp',
                    'logs' => 'logs',
                    'path' => '{tmp}/{logs}/app.log',
                ],
            ],
            [
                [
                    'bar' => ['getValue', 'I\'m a bar {{}}foo bar'],
                ],
                [
                    'foo' => 'bar',
                    'bar' => 'I\'m a {foo} {{}}foo {foo}',
                ],
            ],
            [
                [
                    'bar' => ['getValue', 'I\'m a bar {{foo bar'],
                ],
                [
                    'foo' => 'bar',
                    'bar' => 'I\'m a {foo} {{foo {foo}',
                ],
            ],
            [
                [
                    'bar' => ['getValue', 'I\'m a bar }}foo bar'],
                ],
                [
                    'foo' => 'bar',
                    'bar' => 'I\'m a {foo} }}foo {foo}',
                ],
            ],
            'Twig-like strings are not parameters.' => [
                [
                    'test' => ['getValue', '{% set my_template = "foo" %}'],
                ],
                [
                    'test' => '{% set my_template = "foo" %}',
                ],
            ],
            'Parameters should not have spaces.' => [
                [
                    'test' => ['getValue', '{ foo }'],
                ],
                [
                    'test' => '{ foo }',
                ],
            ],
            'factory class should be resolved' => [
                [
                    'factory' => ['getClass', FooClass::class],
                ],
                [
                    'factory_class' => FooClass::class,
                ],
                [
                    'factory' => [['{factory_class}', 'getInstance']],
                ],
            ],
            'arguments parameter should be resolved' => [
                [
                    'arguments' => ['getArguments', ['bar', ['bar' => 'baz']]],
                ],
                [
                    'foo.arg1' => 'bar',
                ],
                [
                    'arguments' => [
                        stdClass::class,
                        [
                            static function (ObjectDefinitionContract $definition): void {
                                $definition->setArguments(['{foo.arg1}', ['{foo.arg1}' => 'baz']]);
                            },
                        ],
                    ],
                ],
            ],
            'method calls parameter should be resolved' => [
                [
                    'arguments' => ['getMethodCalls', [['foobar', ['bar', ['bar' => 'baz']], false]]],
                ],
                [
                    'foo.method' => 'foobar',
                    'foo.arg1' => 'bar',
                    'foo.arg2' => ['{foo.arg1}' => 'baz'],
                ],
                [
                    'arguments' => [
                        stdClass::class,
                        [
                            static function (ObjectDefinitionContract $definition): void {
                                $definition->addMethodCall('{foo.method}', ['{foo.arg1}', '{foo.arg2}']);
                            },
                        ],
                    ],
                ],
            ],
            'property parameter should be resolved' => [
                [
                    'arguments' => ['getProperties', ['bar' => ['baz', false]]],
                ],
                [
                    'foo.property.name' => 'bar',
                    'foo.property.value' => 'baz',
                ],
                [
                    'arguments' => [
                        stdClass::class,
                        [
                            static function (ObjectDefinitionContract $definition): void {
                                $definition->setProperty('{foo.property.name}', '{foo.property.value}');
                            },
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testResolveExpressionToThrowCircularParameterException(): void
    {
        $container = new ContainerBuilder();

        $container->setParameter('foo', '{bar}')->setPublic(true);
        $container->setParameter('bar', '{foobar}')->setPublic(true);
        $container->setParameter('foobar', '{foo}')->setPublic(true);

        try {
            $this->process($container);

            $container->getParameter('foo');
            self::fail('->resolveValue() throws a CircularParameterException when a parameter has a circular reference');
        } catch (CircularParameterException $e) {
            self::assertEquals('Circular reference detected for parameter [foo]; path: [foo -> bar -> foobar -> foo].', $e->getMessage(), '->resolveValue() throws a CircularParameterException when a parameter has a circular reference');
        }

        $container = new ContainerBuilder();

        $container->setParameter('foo', 'a {bar}')->setPublic(true);
        $container->setParameter('bar', 'a {foobar}')->setPublic(true);
        $container->setParameter('foobar', 'a {foo}')->setPublic(true);

        try {
            $this->process($container);

            $container->getParameter('foo');
            self::fail('->resolveValue() throws a CircularParameterException when a parameter has a circular reference');
        } catch (CircularParameterException $e) {
            self::assertEquals('Circular reference detected for parameter [foo]; path: [foo -> bar -> foobar -> foo].', $e->getMessage(), '->resolveValue() throws a CircularParameterException when a parameter has a circular reference');
        }
    }

    public function testResolveExpressionToThrowRuntimeExceptionOnNoString(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A string value must be composed of strings and/or numbers, but found parameter [key] of type [array] inside a string value [a {key}].');

        $container = new ContainerBuilder();

        $container->setParameter('key', [])->setPublic(true);
        $container->setParameter('foo', 'a {key}')->setPublic(true);

        $this->process($container);

        $container->getParameter('foo');
    }

    public function testResolveExpressionWithAliases(): void
    {
        $container = new ContainerBuilder();

        $container->setParameter('key', 'baz')->setPublic(true);
        $container->setAlias(ContainerInterface::class, '{key}.foo')->setPublic(true);

        $this->process($container);

        self::assertTrue($container->hasAlias('baz.foo'));
    }

    /**
     * @dataProvider provideProcessThrowsExceptionIfStrictModeIsActiveCases
     *
     * @param callable $callback
     * @param string   $key
     * @param string   $type
     *
     * @return void
     */
    public function testProcessThrowsExceptionIfStrictModeIsActive(callable $callback, string $key, string $type): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(\sprintf('The %s [%s] has a dependency on a non-existent parameter [key].', $type, $key));

        $container = new ContainerBuilder();

        $callback($container);

        $this->process($container);
    }

    public static function provideProcessThrowsExceptionIfStrictModeIsActiveCases(): iterable
    {
        return [
            [
                static function (ContainerBuilderContract $container): void {
                    $container->setParameter('foo', '{key}');
                },
                'foo',
                'parameter',
            ],
            [
                static function (ContainerBuilderContract $container): void {
                    $container->bind('{key}', stdClass::class)
                        ->setPublic(true);
                },
                '{key}',
                'service',
            ],
            [
                static function (ContainerBuilderContract $container): void {
                    $container->bind('foo', stdClass::class)
                        ->setPublic(true);
                    $container->setAlias('foo', '{key}')
                        ->setPublic(true);
                },
                '{key}',
                'alias',
            ],
            [
                static function (ContainerBuilderContract $container): void {
                    $definition = $container->singleton('key', stdClass::class);
                    $definition->setArgument(0, '{key}');
                },
                'key',
                'service',
            ],
        ];
    }

    public function testParameterNotFoundExceptionsIsNotThrown(): void
    {
        $container = new ContainerBuilder();

        $definition = $container->singleton($key = 'baz_service_id', stdClass::class);
        $definition->setArgument(0, '{non_existent_param}');

        $this->process($container, false);

        self::assertSame('{non_existent_param}', $container->getDefinition($key)->getArgument(0));
    }

    /**
     * @param \Viserio\Contract\Container\ContainerBuilder $container
     * @param bool                                         $throwException
     */
    private function process(ContainerBuilderContract $container, bool $throwException = true): void
    {
        $pipe = new ResolveParameterPlaceHolderPipe(true, $throwException);

        $pipe->process($container);
    }
}
