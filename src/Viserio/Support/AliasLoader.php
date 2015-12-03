<?php
namespace Viserio\Support;

class AliasLoader
{
    /**
     * The array of class aliases.
     *
     * @var array
     */
    protected $aliases;

    /**
     * Indicates if a loader has been registered.
     *
     * @var bool
     */
    protected $registered = false;

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
        $this->aliases[$class] = $alias;
    }

    /**
     * Register the loader on the auto-loader stack.
     */
    public function register()
    {
        if (!$this->registered) {
            $this->prependToLoaderStack();

            $this->registered = true;
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
        return $this->registered;
    }

    /**
     * Set the "registered" state of the loader.
     *
     * @param bool $value
     */
    public function setRegistered($value)
    {
        $this->registered = $value;
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
