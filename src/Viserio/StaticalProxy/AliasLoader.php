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
     * {@inheritdoc}
     */
    public function load(string $alias): bool
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
     * {@inheritdoc}
     */
    public function alias($classes, string $alias = null): AliasLoaderContract
    {
        if (is_array($classes)) {
            $this->aliases = array_merge($this->aliases, $classes);

            return $this;
        }

        $this->aliases[$classes] = $this->cache[$classes] = $alias;

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function resolveAlias(string $alias)
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
     * {@inheritdoc}
     */
    public function aliasPattern($patterns, string $translation = null)
    {
        if (!is_array($patterns)) {
            $patterns = [$patterns => $translation];
        }

        foreach ($patterns as $patternKey => $resolver) {
            if (!$resolver instanceof Resolver) {
                $resolver = new Resolver($patternKey, $resolver);
            }

            $this->patterns[$patternKey] = $resolver;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAliasPattern(string $pattern, string $translation = null)
    {
        foreach (array_keys($this->patterns) as $patternKey) {
            if ($this->patterns[$patternKey]->matches($pattern, $translation)) {
                unset($this->patterns[$patternKey]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function aliasNamespace(string $class, string $alias)
    {
        $class = trim($class, '\\');
        $alias = trim($alias, '\\');

        $this->namespaces[] = [$class, $alias];
    }

    /**
     * {@inheritdoc}
     */
    public function resolveNamespaceAlias(string $alias)
    {
        foreach ($this->namespaces as $namespace) {
            list($nsClass, $nsAlias) = $namespace;

            if (!$nsAlias || strpos($alias, strval($nsAlias)) === 0) {
                if ($nsAlias) {
                    $alias = substr($alias, strlen($nsAlias) + 1);
                }

                $class = $nsClass . '\\' . $alias;
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function register()
    {
        if (!$this->registered) {
            spl_autoload_register([$this, 'load'], true, true);

            $this->registered = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unregister()
    {
        if ($this->registered) {
            spl_autoload_unregister([$this, 'load']);

            $this->registered = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRegistered(): bool
    {
        return $this->registered;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * {@inheritdoc}
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * Resolves pattern aliases.
     *
     * @param string $alias
     *
     * @return bool
     */
    protected function resolvePatternAlias(string $alias): bool
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
