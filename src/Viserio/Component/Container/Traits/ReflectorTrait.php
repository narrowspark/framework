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

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Viserio\Component\Container\ClassHelper;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Contract\Container\Exception\BindingResolutionException;

trait ReflectorTrait
{
    /**
     * A list of class reflectors.
     *
     * @var ReflectionClass[]
     */
    protected $classReflectors = [];

    /**
     * A list of method reflectors.
     *
     * @var ReflectionMethod[]
     */
    protected $methodReflectors = [];

    /**
     * Get the reflection object for the object or class name.
     *
     * @throws ReflectionException
     */
    protected function getClassReflector(string $class, bool $throw = true): ?ReflectionClass
    {
        $hashClass = ContainerBuilder::getHash($class);

        if (isset($this->classReflectors[$hashClass])) {
            return $this->classReflectors[$hashClass];
        }

        try {
            if (ClassHelper::isClassLoaded($class) !== false) {
                $this->classReflectors[$hashClass] = new ReflectionClass($class);
            }
        } catch (ReflectionException $exception) {
            if ($throw) {
                throw $exception;
            }

            return null;
        }

        return $this->classReflectors[$hashClass] ?? null;
    }

    /**
     * Get the reflection object for a method.
     *
     * @throws \Viserio\Contract\Container\Exception\BindingResolutionException
     */
    protected function getMethodReflector(ReflectionClass $classReflector, string $method): ReflectionFunctionAbstract
    {
        $className = $classReflector->getName();
        $classWithMethod = ContainerBuilder::getHash($className . $method);

        if (isset($this->methodReflectors[$classWithMethod])) {
            return $this->methodReflectors[$classWithMethod];
        }

        try {
            $this->methodReflectors[$classWithMethod] = $classReflector->getMethod($method);
        } catch (ReflectionException $exception) {
            throw new BindingResolutionException(\sprintf('Unable to reflect on method [%s], the method does not exist in class [%s].', $method, $className));
        }

        /** @var ReflectionMethod $methodReflector */
        $methodReflector = $this->methodReflectors[$classWithMethod];

        if (! $methodReflector->isPublic()) {
            throw new BindingResolutionException(\sprintf('Method [%s] of class [%s] must be public.', $method, $className));
        }

        return $methodReflector;
    }

    /**
     * Get the reflection object for a method.
     *
     * @param Closure|string $function
     *
     * @throws \Viserio\Contract\Container\Exception\BindingResolutionException
     */
    protected function getFunctionReflector($function): ReflectionFunction
    {
        try {
            $reflection = new ReflectionFunction($function);
        } catch (ReflectionException $exception) {
            throw new BindingResolutionException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $reflection;
    }
}
