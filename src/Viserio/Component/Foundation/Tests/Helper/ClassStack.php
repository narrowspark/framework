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

namespace Viserio\Component\Foundation\Tests\Helper;

class ClassStack
{
    /** @var array */
    private static $data = [];

    /**
     * Reset state.
     */
    public static function reset(): void
    {
        self::$data = [];
    }

    /**
     * Push a class on the stack.
     */
    public static function add(string $class, bool $bool): void
    {
        self::$data[$class] = $bool;
    }

    /**
     * Return the current class stack.
     *
     * @return string[]
     */
    public static function stack(): array
    {
        return self::$data;
    }

    /**
     * Verify if class exists in the stack.
     */
    public static function has(string $class): bool
    {
        return isset(self::$data[$class]);
    }

    /**
     * Verify if class should exists.
     */
    public static function get(string $class): bool
    {
        return self::$data[$class];
    }
}
