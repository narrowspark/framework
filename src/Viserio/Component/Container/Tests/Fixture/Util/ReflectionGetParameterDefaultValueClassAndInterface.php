<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture\Util;

define('DEFINED', 123);
define('CONSTDEFINED', 'xxx');

const CONSTDEFINED = 456;

interface ReflectionGetParameterDefaultValueInterface
{
    public const DEFINED = 'xyz';
}

class ReflectionGetParameterDefaultValueClassAndInterface
{
    public const PUBLIC_DEFINED       = 'abc';
    protected const PROTECTED_DEFINED = 'abc';
    private const PRIVATE_DEFINED     = 'abc';

    public function method(
        $a,
        $b = self::PUBLIC_DEFINED,
        $c = ReflectionGetParameterDefaultValueClassAndInterface::PUBLIC_DEFINED,
        $d = SELF::PUBLIC_DEFINED,
        $e = ReflectionGetParameterDefaultValueInterface::DEFINED,
        $f = self::UNDEFINED,
        $g = Undefined::ANY,
        $h = DEFINED,
        $i = UNDEFINED,
        $j = CONSTDEFINED
    ): void {
    }

    public function method2(
        $a,
        $b = self::PUBLIC_DEFINED,
        $c = ReflectionGetParameterDefaultValueClassAndInterface::PUBLIC_DEFINED,
        $d = SELF::PUBLIC_DEFINED,
        $e = ReflectionGetParameterDefaultValueClassAndInterface::PROTECTED_DEFINED,
        $f = self::PROTECTED_DEFINED,
        $g = ReflectionGetParameterDefaultValueClassAndInterface::PRIVATE_DEFINED,
        $h = self::PRIVATE_DEFINED,
        $i = self::UNDEFINED,
        $j = ReflectionGetParameterDefaultValueClassAndInterface::UNDEFINED
    ): void {
    }
}
