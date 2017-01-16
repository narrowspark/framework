<?php
declare(strict_types=1);
namespace Viserio\Component\Support;

use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class ClassLoader
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * The registered directories.
     *
     * @var array
     */
    protected static $directories = [];

    /**
     * Indicates if a Autoloader has been registered.
     *
     * @var bool
     */
    protected static $registered = false;

    /**
     * Load the given class file.
     *
     * @param string $class
     *
     * @return bool
     */
    public static function load(string $class): bool
    {
        $class = self::normalizeClass($class);

        foreach (self::$directories as $directory) {
            if (file_exists($path = $directory . '/' . $class)) {
                require_once $path;

                return true;
            }
        }

        return false;
    }

    /**
     * Get the normal file name for a class.
     *
     * @param string $class
     *
     * @return string
     */
    public static function normalizeClass(string $class): string
    {
        if ($class[0] === '\\') {
            $class = mb_substr($class, 1);
        }

        return self::normalizeDirectorySeparator(
            str_replace(['\\', '_'], '/', $class) . '.php'
        );
    }

    /**
     * Register the given class loader on the auto-loader stack.
     *
     * @codeCoverageIgnore
     */
    public static function register()
    {
        if (! self::$registered) {
            self::$registered = spl_autoload_register(
                [static::class, 'load']
            );
        }
    }

    /**
     * Add directories to the class loader.
     *
     * @param string[] $directories
     */
    public static function addDirectories(array $directories)
    {
        self::$directories = array_unique(array_merge(self::$directories, $directories));
    }

    /**
     * Remove directories from the class loader.
     *
     * @param string[] $directories
     */
    public static function removeDirectories(array $directories = null)
    {
        if ($directories === null) {
            self::$directories = [];
        } else {
            self::$directories = array_diff(self::$directories, $directories);
        }
    }

    /**
     * Gets all the directories registered with the loader.
     *
     * @return array
     */
    public static function getDirectories(): array
    {
        return self::$directories;
    }
}
