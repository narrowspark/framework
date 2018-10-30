<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Helper;

class ClassStack
{
    /**
     * @var array
     */
    private static $data = [];

    /**
     * Reset state.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$data = [];
    }

    /**
     * Push a class on the stack.
     *
     * @param string $class
     * @param bool   $bool
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
     *
     * @param string $class
     *
     * @return bool
     */
    public static function has(string $class): bool
    {
        return isset(self::$data[$class]);
    }

    /**
     * Verify if class should exists.
     *
     * @param string $class
     *
     * @return bool
     */
    public static function get(string $class): bool
    {
        return self::$data[$class];
    }
}
