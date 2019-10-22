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

if (! \function_exists('is_class')) {
    /**
     * Verify that the contents of a variable is a class.
     *
     * @param mixed $value
     *
     * @return bool
     */
    function is_class($value): bool
    {
        return \is_string($value) && (class_exists($value) || \preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+)++$/', $value));
    }
}

if (! \function_exists('is_anonymous_class')) {
    /**
     * Verify that the contents of a variable is a class.
     *
     * @param mixed $value
     *
     * @return bool
     */
    function is_anonymous_class($value): bool
    {
        return \strpos(\is_object($value) ? \get_class($value) : (string) $value, "class@anonymous\0") !== false;
    }
}

if (! \function_exists('is_invokable')) {
    /**
     * Verify that the contents of a variable can be called as a function.
     *
     * @param mixed $value
     *
     * @return bool
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
     *
     * @return bool
     */
    function is_method($value): bool
    {
        return (\is_string($value) && \strpos($value, '@') !== false && \method_exists(...\explode('@', $value))) || (\is_array($value) && \count($value) === 2 && \method_exists(...$value));
    }
}

if (! \function_exists('is_static_method')) {
    /**
     * Verify that the contents of a variable is a class static method.
     *
     * @param mixed $value
     *
     * @return bool
     */
    function is_static_method($value): bool
    {
        return \is_string($value) && \strpos($value, '::') !== false && \method_exists(...\explode('::', $value));
    }
}

if (! \function_exists('is_function')) {
    /**
     * Verify that the contents of a variable is a function.
     *
     * @param mixed $value
     *
     * @return bool
     */
    function is_function($value): bool
    {
        return \is_callable($value) && ($value instanceof Closure || (\is_string($value) && \function_exists($value)));
    }
}
