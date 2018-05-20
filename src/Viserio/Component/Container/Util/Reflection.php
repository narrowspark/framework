<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Util;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class Reflection
{
    private static $builtinTypes = [
        'int'      => true,
        'float'    => true,
        'string'   => true,
        'bool'     => true,
        'resource' => true,
        'object'   => true,
        'array'    => true,
        'null'     => true,
        'callable' => true,
        'iterable' => true,
        'mixed'    => true,
    ];

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Check if type is a builtin php type.
     *
     * @param string $type
     *
     * @return bool
     */
    public static function isBuiltinType(string $type): bool
    {
        return isset(self::$builtinTypes[\mb_strtolower($type)]);
    }

    /**
     * Returns the parameter type, like array, callable.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return null|string if no type is found return null
     */
    public static function getParameterType(ReflectionParameter $parameter): ?string
    {
        return $parameter->hasType() ? self::normalizeType((string) $parameter->getType(), $parameter) : null;
    }

    /**
     * Get the default parameter from a method.
     *
     * @param \ReflectionParameter $parameter
     *
     * @throws \ReflectionException when default value is not available or resolvable
     *
     * @return mixed
     */
    public static function getParameterDefaultValue(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueConstant()) {
            $const = $orig = $parameter->getDefaultValueConstantName();
            $pair  = \explode('::', $const);

            if (isset($pair[1])) {
                if (\mb_strtolower($pair[0]) === 'self') {
                    $pair[0] = $parameter->getDeclaringClass()->getName();
                }

                try {
                    $rcc = new ReflectionClassConstant($pair[0], $pair[1]);
                } catch (ReflectionException $excption) {
                    $name = self::toString($parameter);

                    throw new ReflectionException(\sprintf('Unable to resolve constant %s used as default value of %s.', $orig, $name), 0, $excption);
                }

                return $rcc->getValue();
            } elseif (! \defined($const)) {
                $const = \mb_substr((string) \mb_strrchr($const, '\\'), 1);

                if (! defined($const)) {
                    $name = self::toString($parameter);

                    throw new ReflectionException(\sprintf('Unable to resolve constant %s used as default value of %s.', $orig, $name));
                }
            }

            return \constant($const);
        }

        return $parameter->getDefaultValue();
    }

    /**
     * Returns declaring class or trait.
     *
     * @param \ReflectionProperty $property
     *
     * @return \ReflectionClass
     */
    public static function getPropertyDeclaringClass(ReflectionProperty $property): ReflectionClass
    {
        foreach ($property->getDeclaringClass()->getTraits() as $trait) {
            if ($trait->hasProperty($property->getName())) {
                return self::getPropertyDeclaringClass($trait->getProperty($property->getName()));
            }
        }

        return $property->getDeclaringClass();
    }

    /**
     * Returns a readable string from all Reflectors.
     *
     * @param \Reflector $reflector
     *
     * @return string
     */
    public static function toString(Reflector $reflector): string
    {
        if ($reflector instanceof ReflectionClass || $reflector instanceof ReflectionFunction) {
            return $reflector->getName();
        }

        if ($reflector instanceof ReflectionMethod) {
            return $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName();
        }

        if ($reflector instanceof ReflectionProperty) {
            return self::getPropertyDeclaringClass($reflector)->getName() . '::$' . $reflector->getName();
        }

        if ($reflector instanceof ReflectionParameter) {
            return '$' . $reflector->getName() . ' in ' . self::toString($reflector->getDeclaringFunction()) . '()';
        }

        throw new InvalidArgumentException('A not supported reflector was given.');
    }

    /**
     * @param string               $type
     * @param \ReflectionParameter $reflection
     *
     * @return string
     */
    private static function normalizeType(string $type, ReflectionParameter $reflection): string
    {
        $lower = \mb_strtolower($type);

        if ($lower === 'self') {
            return $reflection->getDeclaringClass()->getName();
        } elseif ($lower === 'parent' && $reflection->getDeclaringClass()->getParentClass()) {
            return $reflection->getDeclaringClass()->getParentClass()->getName();
        }

        return $type;
    }
}
