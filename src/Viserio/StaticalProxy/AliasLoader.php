<?php
namespace Viserio\StaticalProxy;

use Viserio\Contracts\StaticalProxy\AliasLoader as AliasLoaderContract;
use Viserio\StaticalProxy\Traits\ExistTrait;

class AliasLoader implements AliasLoaderContract
{
    use ExistTrait;

    /**
     * Array of class aliases.
     *
     * @var array
     */
    private $aliases = [];

    /**
     * @var Resolver[]
     */
    private $patterns = [];

    /**
     * Array of namespaces aliases.
     *
     * @var array
     */
    private $namespaces = [];

    /**
     * Indicates if a loader has been registered.
     *
     * @var bool
     */
    private $registered = false;

    /**
     * All cached resolved aliases.
     *
     * @var array
     */
    private $cache = [];

    /**
     * @var array
     */
    private $resolving = [];

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
     * Resolves an alias.
     *
     * @param string $alias
     *
     * @return boolean
     */
    public function load($alias)
    {
        // Skip recursive aliases if defined
        if (in_array($alias, $this->resolving)) {
            return false;
        }

        // Set it as the resolving class for when
        // we want to block recursive resolving
        $this->resolving[] = $alias;

        if (isset($this->cache[$alias])) {
            // If we already have the alias in the cache don't bother resolving again
            $class = $this->cache[$alias];
        } elseif ($class = $this->resolveAlias($alias)) {
            // We've got a plain alias, now we can skip the others as this
            // is the most powerful one.
        } elseif ($class = $this->resolveNamespaceAlias($alias)) {
            // We've got a namespace alias, we can skip pattern matching.
        } elseif (!$class = $this->resolvePatternAlias($alias)) {
            // Lastly we'll try to resolve it through pattern matching. This is the most
            // expensive match type. Caching is recommended if you use this.
            return false;
        }

        // Remove the resolving class
        array_pop($this->resolving);

        if (!$this->exists($class)) {
            return false;
        }

        // Create the actual alias
        class_alias($class, $alias);

        if (!isset($this->cache[$alias])) {
            $this->cache[$alias] = $class;
        }

        return true;
    }

    /**
     * Add an alias to the loader.
     *
     * @param string|string[] $class
     * @param string|null     $alias
     *
     * @return self
     */
    public function alias($class, $alias = null)
    {
        if (is_array($class)) {
            $this->aliases = array_merge($this->aliases, $class);

            return $this;
        }

        $this->aliases[$class] = $this->cache[$class] = $alias;

        return $this;
    }

    /**
     * Removes an alias.
     *
     */
    public function removeAlias()
    {
        $class = func_get_args();

        foreach ($class as $alias) {
            if (isset($this->aliases[$alias])) {
                unset($this->aliases[$alias], $this->cache[$alias]);
            }
        }
    }

    /**
     * Resolves a plain alias.
     *
     * @param string $alias
     *
     * @return string|boolean
     */
    public function resolveAlias($alias)
    {
        if (
            isset($this->aliases[$alias]) &&
            $this->exists($this->aliases[$alias], true)
        ) {
            return $this->aliases[$alias];
        }

        return false;
    }

    /**
     * Registers a class alias.
     *
     * @param string|string[] $pattern
     * @param string|null     $translation
     */
    public function aliasPattern($pattern, $translation = null)
    {
        if (!is_array($pattern)) {
            $pattern = [$pattern => $translation];
        }

        foreach ($pattern as $patternKey => $resolver) {
            if (!$resolver instanceof Resolver) {
                $resolver = new Resolver($patternKey, $resolver);
            }

            $this->patterns[$patternKey] = $resolver;
        }
    }

    /**
     * Removes an alias pattern.
     *
     * @param string      $pattern
     * @param string|null $translation
     */
    public function removeAliasPattern($pattern, $translation = null)
    {
        foreach (array_keys($this->patterns) as $patternKey) {
            if ($this->patterns[$patternKey]->matches($pattern, $translation)) {
                unset($this->patterns[$patternKey]);
            }
        }
    }

    /**
     * Adds a namespace alias.
     *
     * @param string $class
     * @param string $alias
     */
    public function aliasNamespace($class, $alias)
    {
        $class = trim($class, '\\');
        $alias = trim($alias, '\\');

        $this->namespaces[] = [$class, $alias];
    }

    /**
     * Resolves a namespace alias.
     *
     * @param string $alias Alias
     *
     * @return string|false Class name when resolved
     */
    public function resolveNamespaceAlias($alias)
    {
        foreach ($this->namespaces as $namespace) {
            list($nsClass, $nsAlias) = $namespace;

            if (!$nsAlias || strpos($alias, strval($nsAlias)) === 0) {
                if ($nsAlias) {
                    $alias = substr($alias, strlen($nsAlias) + 1);
                }

                $class = $nsClass.'\\'.$alias;
                $this->resolving[] = $class;

                if ($this->exists($class, true)) {
                    array_pop($this->resolving);

                    return $class;
                }
            }
        }

        return false;
    }

    /**
     * Removes a namespace alias.
     *
     */
    public function removeNamespaceAlias()
    {
        $class = func_get_args();
        $filter = function ($namespace) use ($class) {
            return !in_array($namespace[0], $class);
        };

        $this->namespaces = array_filter($this->namespaces, $filter);
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
     * Register the loader on the auto-loader stack.
     */
    public function register()
    {
        if (!$this->registered) {
            spl_autoload_register([$this, 'load'], true, true);

            $this->registered = true;
        }
    }

    /**
     * Unregisters the autoloader function.
     */
    public function unregister()
    {
        if ($this->registered) {
            spl_autoload_unregister([$this, 'load']);

            $this->registered = false;
        }
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
     * Resolves pattern aliases.
     *
     * @param string $alias
     *
     * @return boolean
     */
    protected function resolvePatternAlias($alias)
    {
        if (isset($this->patterns[$alias]) && $class = $this->patterns[$alias]->resolve($alias)) {
            return $class;
        }

        foreach ($this->patterns as $resolver) {
            if ($class = $resolver->resolve($alias)) {
                return $class;
            }
        }

        return false;
    }
}
