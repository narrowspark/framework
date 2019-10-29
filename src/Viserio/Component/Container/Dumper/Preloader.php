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

namespace Viserio\Component\Container\Dumper;

use ErrorException;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 */
final class Preloader
{
    /**
     * @param array $classes
     *
     * @return void
     */
    public static function preload(array $classes): void
    {
        \set_error_handler(function ($t, $m, $f, $l): void {
            if (\error_reporting() & $t) {
                if (__FILE__ !== $f) {
                    throw new ErrorException($m, 0, $t, $f, $l);
                }

                throw new ReflectionException($m);
            }
        });

        $prev = [];
        $preloaded = [];

        try {
            while ($prev !== $classes) {
                $prev = $classes;

                foreach ($classes as $c) {
                    if (! isset($preloaded[$c])) {
                        self::doPreload($c, $preloaded);
                    }
                }

                $classes = \array_merge(\get_declared_classes(), \get_declared_interfaces(), \get_declared_traits());
            }
        } finally {
            \restore_error_handler();
        }
    }

    /**
     * @param string $class
     * @param array  $preloaded
     *
     * @return void
     */
    private static function doPreload(string $class, array &$preloaded): void
    {
        if (isset($preloaded[$class]) || \in_array($class, ['self', 'static', 'parent'], true)) {
            return;
        }

        $preloaded[$class] = true;

        try {
            $reflectionClass = new ReflectionClass($class);

            if ($reflectionClass->isInternal()) {
                return;
            }

            $reflectionClass->getConstants();
            $reflectionClass->getDefaultProperties();

            if (\PHP_VERSION_ID >= 70400) {
                foreach ($reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
                    if (($type = $reflectionProperty->getType()) && ! $type->isBuiltin()) {
                        self::doPreload($type->getName(), $preloaded);
                    }
                }
            }

            foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                foreach ($reflectionMethod->getParameters() as $reflectionProperty) {
                    if ($reflectionProperty->isDefaultValueAvailable() && $reflectionProperty->isDefaultValueConstant()) {
                        $constantName = $reflectionProperty->getDefaultValueConstantName();

                        if ($i = \strpos($constantName, '::')) {
                            self::doPreload(\substr($constantName, 0, $i), $preloaded);
                        }
                    }

                    if (($type = $reflectionProperty->getType()) && ! $type->isBuiltin()) {
                        self::doPreload($type->getName(), $preloaded);
                    }
                }

                if (($type = $reflectionMethod->getReturnType()) && ! $type->isBuiltin()) {
                    self::doPreload($type->getName(), $preloaded);
                }
            }
        } catch (ReflectionException $exception) {
            // ignore missing classes
        }
    }
}
