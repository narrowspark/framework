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

namespace Viserio\Component\Container\PhpParser\Reflection;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

final class PrivatesCaller
{
    /**
     * Call a private method from a class.
     *
     * @param object|string     $object
     * @param array<int, mixed> $arguments
     *
     * @throws ReflectionException
     */
    public static function callPrivateMethod($object, string $methodName, ...$arguments)
    {
        if (\is_string($object)) {
            $object = (new ReflectionClass($object))->newInstanceWithoutConstructor();
        }

        $methodReflection = self::createAccessibleMethodReflection($object, $methodName);

        return $methodReflection->invoke($object, ...$arguments);
    }

    /**
     * Call a private method with a reference from a class.
     *
     * @param object|string $object
     *
     * @throws ReflectionException
     */
    public static function callPrivateMethodWithReference($object, string $methodName, $argument)
    {
        if (\is_string($object)) {
            $object = (new ReflectionClass($object))->newInstanceWithoutConstructor();
        }

        $methodReflection = self::createAccessibleMethodReflection($object, $methodName);
        $methodReflection->invokeArgs($object, [&$argument]);

        return $argument;
    }

    /**
     * @param object|string $object
     *
     * @throws ReflectionException
     */
    private static function createAccessibleMethodReflection($object, string $methodName): ReflectionMethod
    {
        $methodReflection = new ReflectionMethod($object, $methodName);
        $methodReflection->setAccessible(true);

        return $methodReflection;
    }
}
