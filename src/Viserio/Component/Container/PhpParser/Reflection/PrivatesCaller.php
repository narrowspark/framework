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

namespace Viserio\Component\Container\PhpParser\Reflection;

use ReflectionClass;
use ReflectionMethod;

final class PrivatesCaller
{
    /**
     * Call a private method from a class.
     *
     * @param object|string     $object
     * @param string            $methodName
     * @param array<int, mixed> $arguments
     *
     * @throws \ReflectionException
     *
     * @return mixed
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
     * @param string        $methodName
     * @param mixed         $argument
     *
     * @throws \ReflectionException
     *
     * @return mixed
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
     * @param string        $methodName
     *
     * @throws \ReflectionException
     *
     * @return \ReflectionMethod
     */
    private static function createAccessibleMethodReflection($object, string $methodName): ReflectionMethod
    {
        $methodReflection = new ReflectionMethod($object, $methodName);
        $methodReflection->setAccessible(true);

        return $methodReflection;
    }
}
