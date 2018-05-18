<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Util;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Viserio\Component\Container\Tests\Fixture\Util\ExtendReflectionGetParameterTypeClass;
use Viserio\Component\Container\Tests\Fixture\Util\ExtendReflectionGetPropertyDeclaringClass;
use Viserio\Component\Container\Tests\Fixture\Util\ReflectionGetParameterDefaultValueClassAndInterface;
use Viserio\Component\Container\Tests\Fixture\Util\ReflectionGetParameterTypeClass;
use Viserio\Component\Container\Tests\Fixture\Util\ReflectionGetPropertyDeclaringClass;
use Viserio\Component\Container\Tests\Fixture\Util\ReflectionToStringClass;
use Viserio\Component\Container\Tests\Fixture\Util\Traits\TraitA;
use Viserio\Component\Container\Tests\Fixture\Util\Traits\TraitB;
use Viserio\Component\Container\Tests\Fixture\Util\Traits\TraitC;
use Viserio\Component\Container\Util\Reflection;

class ReflectionTest extends TestCase
{
    /**
     * @dataProvider reflectionToStringProvider
     *
     * @param object $reflection
     * @param string $excepted
     */
    public function testToString(string $excepted, $reflection): void
    {
        self::assertSame($excepted, Reflection::toString($reflection));
    }

    /**
     * @throws \ReflectionException
     *
     * @return array
     */
    public function reflectionToStringProvider(): array
    {
        return [
            [ReflectionToStringClass::class, new ReflectionClass(ReflectionToStringClass::class)],
            [ReflectionToStringClass::class . '::method', new ReflectionMethod(ReflectionToStringClass::class, 'method')],
            ['$param in ' . ReflectionToStringClass::class . '::method()', new ReflectionParameter([ReflectionToStringClass::class, 'method'], 'param')],
            [ReflectionToStringClass::class . '::$var', new ReflectionProperty(ReflectionToStringClass::class, 'var')],
            ['func', new ReflectionFunction('func')],
            ['$param in func()', new ReflectionParameter('func', 'param')],
        ];
    }

    /**
     * @dataProvider reflectionGetParameterTypeProvider
     *
     * @param string      $methodName
     * @param string      $class
     * @param int         $int
     * @param null|string $excepted
     *
     * @return void
     */
    public function testGetParameterType(string $methodName, string $class, ?string $excepted, int $int): void
    {
        $method = new ReflectionMethod($class, $methodName);
        $params = $method->getParameters();

        self::assertSame($excepted, Reflection::getParameterType($params[$int]));
    }

    /**
     * @return array
     */
    public function reflectionGetParameterTypeProvider(): array
    {
        return [
            ['method', ReflectionGetParameterTypeClass::class, 'Viserio\Component\Container\Tests\Fixture\Util\Undeclared', 0],
            ['method', ReflectionGetParameterTypeClass::class, ReflectionToStringClass::class, 1],
            ['method', ReflectionGetParameterTypeClass::class, 'array', 2],
            ['method', ReflectionGetParameterTypeClass::class, 'callable', 3],
            ['method', ReflectionGetParameterTypeClass::class, null, 4],
            ['method', ReflectionGetParameterTypeClass::class, ReflectionToStringClass::class, 5],
            ['method2', ReflectionGetParameterTypeClass::class, 'Viserio\Component\Container\Tests\Fixture\Util\Undeclared', 0],
            ['method2', ReflectionGetParameterTypeClass::class, ReflectionToStringClass::class, 1],
            ['method2', ReflectionGetParameterTypeClass::class, 'array', 2],
            ['method2', ReflectionGetParameterTypeClass::class, 'callable', 3],
            ['method2', ReflectionGetParameterTypeClass::class, ReflectionGetParameterTypeClass::class, 4],
            ['method2', ReflectionGetParameterTypeClass::class, null, 5],
            ['methodExt', ExtendReflectionGetParameterTypeClass::class, ReflectionGetParameterTypeClass::class, 0],
        ];
    }

    /**
     * @dataProvider reflectionGetParameterDefaultValue
     *
     * @param mixed  $excepted
     * @param string $methodName
     * @param string $parameter
     * @param bool   $exception
     * @param string $message
     *
     * @throws \ReflectionException
     *
     * @return void
     */
    public function testGetParameterDefaultValue($excepted, string $methodName, string $parameter, bool $exception= false, string $message = ''): void
    {
        if ($exception === true) {
            try {
                Reflection::getParameterDefaultValue(
                    new ReflectionParameter([ReflectionGetParameterDefaultValueClassAndInterface::class, $methodName], $parameter)
                );
            } catch (ReflectionException $exception) {
                self::assertInstanceOf(ReflectionException::class, $exception);
                self::assertSame($message, $exception->getMessage());

                return;
            }

            $this->fail('No exception was found.');
        } else {
            self::assertSame(
                $excepted,
                Reflection::getParameterDefaultValue(
                    new ReflectionParameter([ReflectionGetParameterDefaultValueClassAndInterface::class, $methodName], $parameter)
                )
            );
        }
    }

    /**
     * @return array
     */
    public function reflectionGetParameterDefaultValue(): array
    {
        return [
            ['abc', 'method', 'b'],
            ['abc', 'method', 'c'],
            ['abc', 'method', 'd'],
            ['xyz', 'method', 'e'],
            [456, 'method', 'j'],
            ['abc', 'method2', 'b'],
            ['abc', 'method2', 'c'],
            ['abc', 'method2', 'd'],
            ['abc', 'method2', 'e'],
            ['abc', 'method2', 'f'],
            ['abc', 'method2', 'g'],
            ['abc', 'method2', 'h'],
            // exceptions
            ['', 'method', 'a', true, 'Internal error: Failed to retrieve the default value'],
            ['', 'method', 'f', true, 'Unable to resolve constant self::UNDEFINED used as default value of $f in ' . ReflectionGetParameterDefaultValueClassAndInterface::class . '::method().'],
            ['', 'method', 'g', true, 'Unable to resolve constant Viserio\Component\Container\Tests\Fixture\Util\Undefined::ANY used as default value of $g in ' . ReflectionGetParameterDefaultValueClassAndInterface::class . '::method().'],
            ['', 'method', 'i', true, 'Unable to resolve constant Viserio\Component\Container\Tests\Fixture\Util\UNDEFINED used as default value of $i in ' . ReflectionGetParameterDefaultValueClassAndInterface::class . '::method().'],
            ['', 'method2', 'a', true, 'Internal error: Failed to retrieve the default value'],
            ['', 'method2', 'i', true, 'Unable to resolve constant self::UNDEFINED used as default value of $i in ' . ReflectionGetParameterDefaultValueClassAndInterface::class . '::method2().'],
            ['', 'method2', 'j', true, 'Unable to resolve constant ' . ReflectionGetParameterDefaultValueClassAndInterface::class . '::UNDEFINED used as default value of $j in ' . ReflectionGetParameterDefaultValueClassAndInterface::class . '::method2().'],
        ];
    }

    /**
     * @dataProvider providerGetPropertyDeclaringClass
     *
     * @param string $excepted
     * @param string $field
     *
     * @throws ReflectionException
     *
     * @return void
     */
    public function testGetPropertyDeclaringClass(string $excepted, string $field): void
    {
        $class = Reflection::getPropertyDeclaringClass(new ReflectionProperty(ExtendReflectionGetPropertyDeclaringClass::class, $field));

        self::assertSame($excepted, $class->getName());
    }

    public function providerGetPropertyDeclaringClass(): array
    {
        return [
            [TraitB::class, 'foo'],
            [TraitA::class, 'bar'],
            [ReflectionGetPropertyDeclaringClass::class, 'own'],
            [TraitC::class, 'baz'],
        ];
    }
}
