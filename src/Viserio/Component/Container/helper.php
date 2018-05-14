<?php
declare(strict_types=1);

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
        return \is_string($value) && \class_exists($value);
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
     * @param mixed $value
     *
     * @return bool
     */
    function is_method($value): bool
    {
        return \is_string($value) && \mb_strpos($value, '@');
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
        return \is_string($value) && \mb_strpos($value, '::');
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

if (! \function_exists('has_overloading')) {
    /**
     * Verify that the contents of a variable is a class method.
     *
     * @param mixed $value
     *
     * @return bool
     */
    function has_overloading($value): bool
    {
        if (! \is_array($value)) {
            return false;
        }

        $class = $value[0];

        return (\is_object($value) || is_class($class)) && (\method_exists($class, '__call') || \method_exists($class, '__callStatic'));
    }
}
