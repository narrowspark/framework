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
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\ResolveParameterPlaceHoldersPipe;
use Viserio\Contract\Container\Exception\CircularParameterException;
use Viserio\Contract\Container\Exception\NotFoundException;
use Viserio\Contract\Container\Exception\RuntimeException;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @small
 */
final class ResolveParameterPlaceHoldersPipeTest extends TestCase
{
    /**
     * @dataProvider provideProcessCases
     *
     * @param string $key
     * @param string $expected
     * @param array  $parameters
     * @param array  $services
     */
    public function testProcess(string $key, string $expected, array $parameters = [], array $services = []): void
    {
        $container = new ContainerBuilder();

        foreach ($parameters as $service => $value) {
            $container->setParameter($service, $value)->setPublic(true);
        }

        foreach ($services as $service => $value) {
            $container->bind($service, $value)->setPublic(true);
        }

        $this->process($container);

        if (\count($services) !== 0) {
            /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
            $definition = $container->getDefinition($key);
        } else {
            /** @var \Viserio\Component\Container\Definition\ParameterDefinition $definition */
            $definition = $container->getParameter($key);
        }

        self::assertSame($definition->getValue(), $expected);
    }

    public function provideProcessCases(): iterable
    {
        return [
            [
                'test',
                'bar',
                ['test' => 'bar'],
            ],
            [
                'foo',
                'baz',
                [
                    'baz' => 'baz',
                ],
                [
                    'foo' => '{baz}',
                ],
            ],
            [
                'path',
                '/private/tmp/logs/app.log',
                [
                    'tmp' => '/private/tmp',
                    'logs' => 'logs',
                    'path' => '{tmp}/{logs}/app.log',
                ],
            ],
            [
                'bar',
                'I\'m a bar %%foo bar',
                [
                    'foo' => 'bar',
                ],
                [
                    'bar' => 'I\'m a {foo} %%foo {foo}',
                ],
            ],
            'Twig-like strings are not parameters.' => [
                'test',
                '{% set my_template = "foo" %}',
                [
                    'test' => '{% set my_template = "foo" %}',
                ],
            ],
            'Parameters should not have spaces.' => [
                'test',
                '{ foo }',
                [
                    'test' => '{ foo }',
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
     *
     * @return void
     */
    public function testProcessThrowsExceptionIfStrictModeIsActive(callable $callback, string $key): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(\sprintf('The service or parameter [%s] has a dependency on a non-existent service or parameter [key].', $key));

        $container = new ContainerBuilder();

        $callback($container);

        $this->process($container);
    }

    public function provideProcessThrowsExceptionIfStrictModeIsActiveCases(): iterable
    {
        return [
            [
                static function (ContainerBuilderContract $container): void {
                    $container->setParameter('foo', '{key}');
                },
                'foo',
            ],
            [
                static function (ContainerBuilderContract $container): void {
                    $container->bind('{key}', \stdClass::class)->setPublic(true);
                },
                '{key}',
            ],
            [
                static function (ContainerBuilderContract $container): void {
                    $container->bind('foo', \stdClass::class)->setPublic(true);
                    $container->setAlias('foo', '{key}')->setPublic(true);
                },
                '{key}',
            ],
        ];
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new ResolveParameterPlaceHoldersPipe();

        $pipe->process($container);
    }
}
