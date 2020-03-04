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

namespace Viserio\Component\Container\Traits;

use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;
use Viserio\Contract\Container\Exception\RuntimeException;

trait ReflectorHelpersTrait
{
    /**
     * Returns the type of a reflector.
     *
     * @return null|string The FQCN or builtin name of the type hint, or null when the type hint references an invalid self|parent context
     */
    protected function getTypeHint(
        ReflectionFunctionAbstract $reflectionFunction,
        ?ReflectionParameter $reflectionParameter = null,
        bool $noBuiltin = false
    ): ?string {
        if ($reflectionParameter instanceof ReflectionParameter) {
            $type = $reflectionParameter->getType();
        } else {
            $type = $reflectionFunction->getReturnType();
        }

        if (! $type) {
            return null;
        }

        if (! \is_string($type)) {
            $name = $type->getName();

            if ($type->isBuiltin()) {
                return $noBuiltin ? null : $name;
            }
        }

        $lcName = \strtolower($name);
        $prefix = $noBuiltin ? '' : '\\';

        if ('self' !== $lcName && 'parent' !== $lcName) {
            return $prefix . $name;
        }

        if (! $reflectionFunction instanceof ReflectionMethod) {
            return null;
        }

        if ($lcName === 'self') {
            return $prefix . $reflectionFunction->getDeclaringClass()->name;
        }

        if ($parent = $reflectionFunction->getDeclaringClass()->getParentClass()) {
            return $prefix . $parent->name;
        }

        return null;
    }

    /**
     * Get the reflection arguments.
     *
     * @param ReflectionClass|ReflectionObject $reflection
     */
    protected function getConstructor(ReflectionClass $reflection, string $errorMessage): ?ReflectionFunctionAbstract
    {
        /** @var ReflectionMethod $reflectionMethod */
        $reflectionMethod = $reflection->getConstructor();

        if ($reflectionMethod === null) {
            return null;
        }

        if (! $reflectionMethod->isPublic()) {
            throw new RuntimeException($errorMessage);
        }

        return $reflectionMethod;
    }
}
