<?php
namespace Viserio\Support;

class Autoloader
{
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

        foreach (self::getDirectories() as $directory) {
            if (file_exists($path = $directory . DIRECTORY_SEPARATOR . $class)) {
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
            $class = substr($class, 1);
        }

        return str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $class) . '.php';
    }

    /**
     * Register the given class loader on the auto-loader stack.
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
     * @param string|array|null $directories
     */
    public static function addDirectories($directories)
    {
        self::$directories = array_unique(array_merge(self::$directories, (array) $directories));
    }

    /**
     * Remove directories from the class loader.
     *
     * @param string|array|null $directories
     */
    public static function removeDirectories($directories = null)
    {
        if ($directories === null) {
            self::$directories = [];
        } else {
            self::$directories = array_diff(self::$directories, (array) $directories);
        }
    }

    /**
     * Gets all the directories registered with the loader.
     *
     * @return array
     */
    public static function getDirectories()
    {
        return self::$directories;
    }
}
