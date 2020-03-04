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

namespace Narrowspark\Benchmark\Container;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @BeforeClassMethods({"clearCache"}, extend=true)
 * @Iterations(50)
 * @Revs({1000})
 * @OutputTimeUnit("microseconds", precision=3)
 */
abstract class ContainerBenchCase
{
    public static function getCacheDir(): string
    {
        return \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'cache';
    }

    public static function clearCache(): void
    {
        if (\file_exists(self::getCacheDir())) {
            (new Filesystem())->remove(self::getCacheDir());
        }

        if (! \mkdir($concurrentDirectory = self::getCacheDir()) && ! \is_dir($concurrentDirectory)) {
            throw new RuntimeException(\sprintf('Directory "%s" was not created.', $concurrentDirectory));
        }
    }

    abstract public function initOptimized();

    abstract public function initUnoptimized();

    /**
     * Return a single instance of a class from the container.
     *
     * @Groups({"optimized"})
     * @BeforeMethods({"initOptimized"}, extend=true)
     */
    abstract public function benchGetOptimized();

    /**
     * Return a single instance of a class from an unoptimized container.
     *
     * @Groups({"unoptimized"})
     * @BeforeMethods({"initUnoptimized"}, extend=true)
     */
    abstract public function benchGetUnoptimized();

    /**
     * Return a new instance (prototype) of a class from the container.
     *
     * @Groups({"prototype"})
     * @BeforeMethods({"initOptimized"}, extend=true)
     */
    abstract public function benchGetPrototype();
}
