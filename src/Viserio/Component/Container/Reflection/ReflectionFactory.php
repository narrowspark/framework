<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Reflection;

use Closure;
use ReflectionException;
use ReflectionFunction as BaseReflectionFunction;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionFunction;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;
use RuntimeException as BaseRuntimeException;
use Viserio\Component\Contract\Container\Exception\BindingResolutionException;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

final class ReflectionFactory
{
    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Returns a array with the found class and method from string.
     *
     * @param string $value
     *
     * @return array
     */
    public static function parseStringMethod(string $value): array
    {
        return \explode('@', $value, 2);
    }

    /**
     * Get the reflection arguments.
     *
     * @param \ReflectionFunction|
     *        \Roave\BetterReflection\Reflection\ReflectionClass|
     *        \Roave\BetterReflection\Reflection\ReflectionFunction|
     *        \Roave\BetterReflection\Reflection\ReflectionMethod $reflection
     *
     * @throws \Viserio\Component\Contract\Container\Exception\InvalidArgumentException
     *
     * @return \ReflectionParameter[]|\Roave\BetterReflection\Reflection\ReflectionParameter[]
     */
    public static function getParameters($reflection): array
    {
        if (! $reflection instanceof BaseReflectionFunction &&
            ! $reflection instanceof ReflectionFunction &&
            ! $reflection instanceof ReflectionMethod &&
            ! $reflection instanceof ReflectionClass &&
            ! $reflection instanceof ReflectionObject
        ) {
            throw new InvalidArgumentException(\sprintf(
                'The $reflection only supports ReflectionFunction, Roave\BetterReflection\Reflection\ReflectionFunction,' .
                ' Roave\BetterReflection\Reflection\ReflectionMethod, Roave\BetterReflection\Reflection\ReflectionObject and' .
                ' Roave\BetterReflection\Reflection\ReflectionClass, [%s] given.',
                \is_object($reflection) ? \get_class($reflection) : \gettype($reflection)
            ));
        }

        if ($reflection instanceof ReflectionClass || $reflection instanceof ReflectionObject) {
            $reflectionParameters = self::getClassParameters($reflection);
        } else {
            $reflectionParameters = $reflection->getParameters();
        }

        return $reflectionParameters;
    }

    /**
     * Get the reflection object for the object or class name.
     *
     * @param object|string $class
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     *
     * @return \Roave\BetterReflection\Reflection\ReflectionClass
     */
    public static function getClassReflector($class): ReflectionClass
    {
        if (! \is_string($class) && ! \is_object($class)) {
            throw new InvalidArgumentException(\sprintf(
                'The $class parameter must be of type class string or object, [%s] given.',
                \gettype($class)
            ));
        }

        try {
            if (\is_string($class)) {
                $reflection = ReflectionClass::createFromName($class);
            } else {
                $reflection = ReflectionClass::createFromInstance($class);
            }
        } catch (ReflectionException | BaseRuntimeException $exception) {
            throw new BindingResolutionException(
                \sprintf(
                    'Unable to reflect on the class [%s], does the class exist and is it properly autoloaded?',
                    \is_object($class) ? \get_class($class) : $class
                )
            );
        }

        if (! $reflection->isInstantiable()) {
            throw new BindingResolutionException(\sprintf('The class [%s] is not instantiable.', $reflection->getName()));
        }

        return $reflection;
    }

    /**
     * Get the reflection object for a method.
     *
     * @param array|string $method The formatting for the string looks like Class@Method or Class::Method
     *                             and for the array [[Class, 'Method'] or [new Class, 'Method']]
     *
     * @throws \ReflectionException
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\InvalidArgumentException
     *
     * @return \Roave\BetterReflection\Reflection\ReflectionMethod
     */
    public static function getMethodReflector($method): ReflectionMethod
    {
        if (! \is_string($method) && ! \is_array($method)) {
            throw new InvalidArgumentException(\sprintf(
                'The $method parameter must be of type string [Class@Method or Class::Method] or array [[Class, \'Method\'] or [new Class, \'Method\']], [%s] given.',
                \is_object($method) ? \get_class($method) : \gettype($method)
            ));
        }

        if (\is_method($method)) {
            [$class, $method] = self::parseStringMethod($method);
        } elseif (is_static_method($method)) {
            [$class, $method] = \explode('::', $method, 2);
        } elseif (\is_array($method) && (\is_callable($method) || is_invokable($method[0]))) {
            [$class, $method] = $method;
        } else {
            throw new InvalidArgumentException('No method found; The $method parameter must be of type string [Class@Method or Class::Method] or array [[Class, \'Method\'] or [new Class, \'Method\']].');
        }

        try {
            if (\is_string($class)) {
                $reflection = ReflectionMethod::createFromName($class, $method);
            } else {
                $reflection = ReflectionMethod::createFromInstance($class, $method);
            }
        } catch (IdentifierNotFound $exception) {
            throw new BindingResolutionException(
                \sprintf(
                    'Unable to reflect on the method [[\'%s\', \'%s\']], does the class exist and is it properly autoloaded?',
                    \is_object($class) ? \get_class($class) : $class,
                    $method
                )
            );
        }

        return $reflection;
    }

    /**
     * Get the reflection object for a method.
     *
     * @param \Closure|string $function
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     *
     * @return \ReflectionFunction|\Roave\BetterReflection\Reflection\ReflectionFunction
     */
    public static function getFunctionReflector($function)
    {
        try {
            // Remove this Closure if check, if https://github.com/Roave/BetterReflection/issues/426 is fixed.
            if ($function instanceof Closure) {
                $reflection = new BaseReflectionFunction($function);
            } else {
                // remove this try/catch if https://github.com/Roave/BetterReflection/pull/373#pullrequestreview-64623749 is merged
                try {
                    $reflection = ReflectionFunction::createFromName($function);
                } catch (IdentifierNotFound $exception) {
                    // retry with the php ReflectionFunction
                    $reflection = new BaseReflectionFunction($function);
                }
            }
        } catch (ReflectionException $exception) {
            throw new BindingResolutionException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $reflection;
    }

    /**
     * Get the class reflection parameters.
     *
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $reflectionClass
     *
     * @return \Roave\BetterReflection\Reflection\ReflectionParameter[]
     */
    private static function getClassParameters(ReflectionClass $reflectionClass): array
    {
        try {
            $reflectionMethod = $reflectionClass->getConstructor();
        } catch (\OutOfBoundsException $exception) {
            $reflectionMethod = null;
        }

        if ($reflectionMethod !== null) {
            return $reflectionMethod->getParameters();
        }

        return [];
    }
}
