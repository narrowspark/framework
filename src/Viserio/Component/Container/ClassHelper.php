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

namespace Viserio\Component\Container;

use ReflectionException;
use ReflectionProperty;

/**
 * @internal
 */
final class ClassHelper
{
    /** @var int */
    private static $autoloadLevel = 0;

    /** @var string */
    private static $autoloadedClass;

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Check if class, trait or interface is loaded.
     *
     * @throws ReflectionException when a parent class/interface/trait is not found
     */
    public static function isClassLoaded(string $class): bool
    {
        $loaded = \class_exists($class, false) || \interface_exists($class, false) || \trait_exists($class, false);
        $exists = $loaded;

        if (! $exists) {
            if (self::$autoloadLevel++ === 0) {
                \spl_autoload_register(self::class . '::throwOnRequiredClass');
            }

            $autoloadedClass = self::$autoloadedClass;
            self::$autoloadedClass = $class;

            try {
                $exists = \class_exists($class) || \interface_exists($class, false) || \trait_exists($class, false);
            } catch (ReflectionException $exception) {
                throw $exception;
            } finally {
                self::$autoloadedClass = $autoloadedClass;

                if (--self::$autoloadLevel === 0) {
                    \spl_autoload_unregister(self::class . '::throwOnRequiredClass');
                }
            }
        }

        return $exists;
    }

    /**
     * @internal
     *
     * @throws ReflectionException When $class is not found and is required
     */
    public static function throwOnRequiredClass($class): void
    {
        if (self::$autoloadedClass === $class) {
            return;
        }

        $e = new ReflectionException("Class {$class} not found");
        $trace = $e->getTrace();
        $autoloadFrame = [
            'function' => 'spl_autoload_call',
            'args' => [$class],
        ];
        $i = 1 + \array_search($autoloadFrame, $trace, true);

        if (isset($trace[$i]['function']) && ! isset($trace[$i]['class'])) {
            switch ($trace[$i]['function']) {
                case 'get_class_methods':
                case 'get_class_vars':
                case 'get_parent_class':
                case 'is_a':
                case 'is_subclass_of':
                case 'class_exists':
                case 'class_implements':
                case 'class_parents':
                case 'trait_exists':
                case 'defined':
                case 'interface_exists':
                case 'method_exists':
                case 'property_exists':
                case 'is_callable':
                    return;
            }
            $props = [
                'file' => $trace[$i]['file'],
                'line' => $trace[$i]['line'],
                'trace' => \array_slice($trace, (int) 1 + $i),
            ];

            foreach ($props as $p => $v) {
                $r = new ReflectionProperty('Exception', $p);
                $r->setAccessible(true);
                $r->setValue($e, $v);
            }
        }

        throw $e;
    }
}
