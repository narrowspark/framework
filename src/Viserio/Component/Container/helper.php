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

if (! \function_exists('is_class')) {
    /**
     * Verify that the contents of a variable is a class.
     */
    function is_class($value): bool
    {
        return \is_string($value) && (class_exists($value) || \preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+)++$/', $value));
    }
}

if (! \function_exists('is_anonymous_class')) {
    /**
     * Verify that the contents of a variable is a class.
     */
    function is_anonymous_class($value): bool
    {
        return \strpos(\is_object($value) ? \get_class($value) : (string) $value, "class@anonymous\0") !== false;
    }
}

if (! \function_exists('is_invokable')) {
    /**
     * Verify that the contents of a variable can be called as a function.
     */
    function is_invokable($value): bool
    {
        return (\is_object($value) || is_class($value)) && \method_exists($value, '__invoke');
    }
}

if (! \function_exists('is_method')) {
    /**
     * Verify that the contents of a variable is a class method.
     *
     * @param array<object|string, string>|string $value
     */
    function is_method($value): bool
    {
        return (\is_string($value) && \strpos($value, '@') !== false && \method_exists(...\explode('@', $value))) || (\is_array($value) && \count($value) === 2 && \method_exists(...$value));
    }
}

if (! \function_exists('is_static_method')) {
    /**
     * Verify that the contents of a variable is a class static method.
     */
    function is_static_method($value): bool
    {
        return \is_string($value) && \strpos($value, '::') !== false && \method_exists(...\explode('::', $value));
    }
}

if (! \function_exists('is_function')) {
    /**
     * Verify that the contents of a variable is a function.
     */
    function is_function($value): bool
    {
        return \is_callable($value) && ($value instanceof Closure || (\is_string($value) && \function_exists($value)));
    }
}
