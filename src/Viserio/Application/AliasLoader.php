<?php
namespace Viserio\Application;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

/**
 * AliasLoader.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3-dev
 */
class AliasLoader
{
    /**
     * The array of class aliases.
     *
     * @var array
     */
    protected $aliases;

    /**
     * Whether or not the loader has been registered.
     *
     * @var bool
     */
    protected $isRegistered = false;

    /**
     * The singleton instance of the loader.
     *
     * @var \Viserio\Application\AliasLoader
     */
    protected static $instance;

    /**
     * Create a new AliasLoader instance.
     *
     * @param array $aliases
     */
    public function __construct(array $aliases = [])
    {
        $this->aliases = $aliases;
    }

    /**
     * Get or create the singleton alias loader instance.
     *
     * @param array $aliases
     *
     * @return \Viserio\Application\AliasLoader
     */
    public static function getInstance(array $aliases = [])
    {
        if (static::$instance === null) {
            return static::$instance = new static($aliases);
        }

        $aliases = array_merge(static::$instance->getAliases(), $aliases);

        static::$instance->setAliases($aliases);

        return static::$instance;
    }

    /**
     * Load a class alias if it is registered.
     *
     * @param string $alias
     *
     * @return bool|null
     */
    public function load($alias)
    {
        if (isset($this->aliases[$alias])) {
            return class_alias($this->aliases[$alias], $alias);
        }

        return;
    }

    /**
     * Add an alias to the loader.
     *
     * @param string $class
     * @param string $alias
     */
    public function alias($class, $alias)
    {
        // Ensure aliases are only added once
        if (isset($this->aliases[$class])) {
            throw new \RuntimeException("The alias, {$class}, has already been added and cannot be modified.");
        }

        $this->aliases[$class] = $alias;

        return $this;
    }

    /**
     * Register the loader on the auto-loader stack.
     */
    public function register()
    {
        if (!$this->isRegistered) {
            $this->prependToLoaderStack();

            $this->isRegistered = true;
        }
    }

    /**
     * Prepend the load method to the auto-loader stack.
     */
    protected function prependToLoaderStack()
    {
        spl_autoload_register([$this, 'load'], true, true);
    }

    /**
     * Get the registered aliases.
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Set the registered aliases.
     *
     * @param array $aliases
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * Indicates if the loader has been registered.
     *
     * @return bool
     */
    public function isRegistered()
    {
        return $this->isRegistered;
    }

    /**
     * Set the "registered" state of the loader.
     *
     * @param bool $value
     */
    public function setRegistered($value)
    {
        $this->isRegistered = $value;
    }

    /**
     * Set the value of the singleton alias loader.
     *
     * @param \Viserio\Application\AliasLoader $loader
     */
    public static function setInstance($loader)
    {
        static::$instance = $loader;
    }

    /**
     * Clone method.
     */
    private function __clone()
    {
        //
    }
}
