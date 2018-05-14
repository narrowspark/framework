<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest;

use PHPUnit\Framework\TestCase;
use ReflectionFunction as BaseReflectionFunction;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionFunction;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Viserio\Component\Container\Reflection\ReflectionFactory;
use Viserio\Component\Container\Tests\Fixture\ContainerConcreteFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerDefaultValueFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerImplementationTwoFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerPrivateConstructor;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Contract\Container\Exception\BindingResolutionException;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class ReflectionFactoryTest extends TestCase
{
    /**
     * @param mixed $class
     * @param mixed $expected
     *
     * @dataProvider getClassDataProvider
     */
    public function testGetClassReflector($class, $expected): void
    {
        $reflection = ReflectionFactory::getClassReflector($class);

        static::assertInstanceOf(ReflectionClass::class, $reflection);
        static::assertSame($expected, $reflection->getName());
    }

    public function getClassDataProvider(): array
    {
        return [
            [ContainerConcreteFixture::class, ContainerConcreteFixture::class],
            ['Viserio\Component\Container\Tests\Fixture\ContainerConcreteFixture', ContainerConcreteFixture::class],
            [new ContainerConcreteFixture(), ContainerConcreteFixture::class],
        ];
    }

    public function testGetClassReflectorThrowsBindingResolutionExceptionOnNotFoundClass(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Unable to reflect on the class [A], does the class exist and is it properly autoloaded?');

        ReflectionFactory::getClassReflector('A');
    }

    public function testGetClassReflectorThrowsBindingResolutionExceptionOnUninstantiableClass(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('The class [Viserio\Component\Container\Tests\Fixture\ContainerPrivateConstructor] is not instantiable.');

        ReflectionFactory::getClassReflector(ContainerPrivateConstructor::class);
    }

    public function testGetClassReflectorThrowsInvalidArgumentExceptionOnWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The $class parameter must be of type class string or object, [array] given.');

        ReflectionFactory::getClassReflector([]);
    }

    /**
     * @param mixed $method
     * @param mixed $expectedClass
     * @param mixed $expectedMethod
     *
     * @dataProvider getMethodDataProvider
     */
    public function testGetMethodReflector($method, $expectedClass, $expectedMethod): void
    {
        $reflection = ReflectionFactory::getMethodReflector($method);

        static::assertInstanceOf(ReflectionMethod::class, $reflection);
        static::assertSame($expectedClass, $reflection->getImplementingClass()->getName());
        static::assertSame($expectedMethod, $reflection->getName());
    }

    public function getMethodDataProvider(): array
    {
        return [
            [FactoryClass::class . '@create', FactoryClass::class, 'create'],
            [[FactoryClass::class, 'create'], FactoryClass::class, 'create'],
            ['Viserio\Component\Container\Tests\Fixture\FactoryClass@create', FactoryClass::class, 'create'],
            [['Viserio\Component\Container\Tests\Fixture\FactoryClass', 'create'], FactoryClass::class, 'create'],
            [[new FactoryClass(), 'create'], FactoryClass::class, 'create'],
            [FactoryClass::class . '::staticCreate', FactoryClass::class, 'staticCreate'],
            [[FactoryClass::class, 'staticCreate'], FactoryClass::class, 'staticCreate'],
            ['Viserio\Component\Container\Tests\Fixture\FactoryClass@staticCreate', FactoryClass::class, 'staticCreate'],
            [['Viserio\Component\Container\Tests\Fixture\FactoryClass', 'staticCreate'], FactoryClass::class, 'staticCreate'],
            [[new FactoryClass(), 'staticCreate'], FactoryClass::class, 'staticCreate'],
        ];
    }

    public function testGetMethodReflectorThrowsInvalidArgumentExceptionOnWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The $method parameter must be of type string [Class@Method or Class::Method] or array [[Class, \'Method\'] or [new Class, \'Method\']], [NULL] given.');

        ReflectionFactory::getMethodReflector(null);
    }

    public function testGetMethodReflectorThrows(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Unable to reflect on the method [[\'A\', \'test\']], does the class exist and is it properly autoloaded?');

        ReflectionFactory::getMethodReflector('A::test');
    }

    /**
     * @param mixed $function
     * @param mixed $expectedFunction
     * @param mixed $expectedReflectionClass
     *
     * @dataProvider getFunctionDataProvider
     */
    public function testGetFunctionReflector($function, $expectedFunction, $expectedReflectionClass): void
    {
        $reflection = ReflectionFactory::getFunctionReflector($function);

        static::assertInstanceOf($expectedReflectionClass, $reflection);
        static::assertSame($expectedFunction, $reflection->getName());
    }

    public function getFunctionDataProvider(): array
    {
        return [
            ['time', 'time', BaseReflectionFunction::class],
            ['is_method', 'is_method', ReflectionFunction::class],
            [
                function () {
                    return 'test';
                },
                'Viserio\Component\Container\Tests\UnitTest\{closure}',
                BaseReflectionFunction::class,
            ],
        ];
    }

    public function testGetFunctionReflectorThrows(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Function test() does not exist');

        ReflectionFactory::getFunctionReflector('test');
    }

    /**
     * @param mixed $reflection
     * @param int   $expectedCount
     *
     * @dataProvider getParametersDataProvider
     */
    public function testGetParameters($reflection, int $expectedCount): void
    {
        $parameters = ReflectionFactory::getParameters($reflection);

        static::assertCount($expectedCount, $parameters);
    }

    public function getParametersDataProvider(): array
    {
        return [
            [ReflectionFactory::getClassReflector(ContainerDefaultValueFixture::class), 2],
            [ReflectionFactory::getClassReflector(ContainerImplementationTwoFixture::class), 0],
            [ReflectionFactory::getFunctionReflector(function ($a) {
            }), 1],
            [ReflectionFactory::getMethodReflector(FactoryClass::class . '@returnsParameters'), 2],
            [ReflectionFactory::getMethodReflector(FactoryClass::class . '::staticCreateWitArg'), 1],
        ];
    }

    public function testGetParametersThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The $reflection only supports ReflectionFunction, Roave\BetterReflection\Reflection\ReflectionFunction,' .
            ' Roave\BetterReflection\Reflection\ReflectionMethod, Roave\BetterReflection\Reflection\ReflectionObject' .
            ' and Roave\BetterReflection\Reflection\ReflectionClass, [string] given.');

        ReflectionFactory::getParameters('test');
    }
}
