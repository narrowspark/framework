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

namespace Viserio\Component\Container\Tests\IntegrationTest;

use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;
use PHPUnit\Framework\Assert;
use stdClass;
use Viserio\Component\Container\AbstractCompiledContainer;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Dumper\PhpDumper;
use Viserio\Component\Container\Tests\Fixture\Invoke\InvokeCallableClass;
use Viserio\Component\Container\Tests\Fixture\Method\ClassWithMethods;

/**
 * @internal
 *
 * @small
 */
final class ContainerCallTest extends BaseContainerTest
{
    /** @var string */
    protected const COMPILATION_DIR = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Compiled' . \DIRECTORY_SEPARATOR . 'Call' . \DIRECTORY_SEPARATOR;

    protected function tearDown(): void
    {
        parent::tearDown();

        \array_map(static function ($value): void {
            @\unlink($value);
        }, \glob(self::COMPILATION_DIR . '*'));

        @\rmdir(self::COMPILATION_DIR);
    }

    public function testNoParameters(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className);

        $result = $container->call(function () {
            return 42;
        });

        self::assertEquals(42, $result);
    }

    public function testParametersOrdered(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className);

        $result = $container->call(function ($foo, $bar) {
            return $foo . $bar;
        }, ['foo', 'bar']);

        self::assertEquals('foobar', $result);
    }

    public function testParametersIndexedByName(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className);

        $result = $container->call(function ($foo, $bar) {
            return $foo . $bar;
        }, [
            // Reverse order: should still work
            'bar' => 'buzz',
            'foo' => 'fizz',
        ]);

        self::assertEquals('fizzbuzz', $result);
    }

    public function testParameterWithDefinitionsIndexed(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className, static function (ContainerBuilder $builder): void {
            $builder->setParameter('bar', 'bam');
        });

        $result = $container->call(function ($foo, $bar) {
            Assert::assertInstanceOf('stdClass', $bar);

            return $foo;
        }, [
            'bar' => $container->make('stdClass'),
            'foo' => $container->getParameter('bar'),
        ]);

        self::assertEquals('bam', $result);
    }

    public function testParameterWithDefinitionsNotIndexed(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className, static function (ContainerBuilder $builder): void {
            $builder->setParameter('bar', 'bam');
        });

        $result = $container->call(function ($foo, $bar) {
            Assert::assertInstanceOf('stdClass', $bar);

            return $foo;
        }, [$container->getParameter('bar'), $container->make('stdClass')]);

        self::assertEquals('bam', $result);
    }

    public function testParameterDefaultValue(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className);

        $result = $container->call(function ($foo = 'hello') {
            return $foo;
        });

        self::assertEquals('hello', $result);
    }

    public function testParameterExplicitValueOverridesDefaultValue(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className);

        $result = $container->call(static function ($foo = 'hello') {
            return $foo;
        }, [
            'foo' => 'test',
        ]);

        self::assertEquals('test', $result);

        $result = $container->call(static function ($foo = 'hello') {
            return $foo;
        }, ['test']);

        self::assertEquals('test', $result);
    }

    public function testParameterFromTypeHint(): void
    {
        $value = new stdClass();
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className, static function (ContainerBuilder $builder) use ($value): void {
            $builder->bind('stdClass', $value)
                ->setPublic(true);
        });

        $result = $container->call(static function (stdClass $foo) {
            return $foo;
        });

        self::assertEquals($value, $result);
    }

    public function testCallWithObjectMethod(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className);

        $object = new ClassWithMethods();
        $result = $container->call([$object, 'foo']);

        self::assertEquals(42, $result);
    }

    public function testCreatesAndCallsClassMethodsUsingContainer(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className, static function (ContainerBuilder $builder): void {
            $builder->bind(ClassWithMethods::class, new ClassWithMethods())
                ->setPublic(true);
        });

        $result = $container->call(ClassWithMethods::class . '@foo');

        self::assertEquals(42, $result);
    }

    public function testCallsStaticMethods(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className);

        $class = ClassWithMethods::class;
        $result = $container->call([$class, 'bar']);

        self::assertEquals(24, $result);
    }

    public function testCallsInvokableObject(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className);

        $result = $container->call(new InvokeCallableClass());

        self::assertEquals(42, $result);
    }

    public function testCreatesAndCallsInvokableObjectsUsingContainer(): void
    {
        $container = $this->getCompiledContainer(\ucfirst(__FUNCTION__), static function (ContainerBuilder $builder): void {
            $builder->bind(InvokeCallableClass::class, new InvokeCallableClass())
                ->setPublic(true);
        });

        $result = $container->call(InvokeCallableClass::class);

        self::assertEquals(42, $result);
    }

    public function testCallsFunction(): void
    {
        $className = \ucfirst(__FUNCTION__);

        $container = $this->getCompiledContainer($className);

        $result = $container->call('callFunctionTestFunction', [
            'str' => 'foo',
        ]);

        self::assertEquals(3, $result);
    }

    public function testNotEnoughParametersGivenForCallable(): void
    {
        $this->expectException(NotEnoughParametersException::class);
        $this->expectExceptionMessage('Unable to invoke the callable because no value was given for parameter 1 ($foo)');

        $container = $this->getCompiledContainer(\ucfirst(__FUNCTION__));

        $container->call(function ($foo): void {
        });
    }

    public function testNotACallable(): void
    {
        $this->expectException(NotCallableException::class);
        $this->expectExceptionMessage('\'foo\' is neither a callable nor a valid container entry');

        $container = $this->getCompiledContainer(\ucfirst(__FUNCTION__));

        $container->call('foo');
    }

    /**
     * @param string        $className
     * @param null|callable $callback
     *
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return \Viserio\Component\Container\AbstractCompiledContainer
     */
    private function getCompiledContainer(string $className, callable $callback = null): AbstractCompiledContainer
    {
        if ($callback !== null) {
            $callback($this->containerBuilder);
        }

        $this->containerBuilder->compile();

        PhpDumper::dumpCodeToFile(self::COMPILATION_DIR . $className . '.php', (new PhpDumper($this->containerBuilder))->dump(['class' => $className]));

        require self::COMPILATION_DIR . $className . '.php';

        return new $className();
    }
}
