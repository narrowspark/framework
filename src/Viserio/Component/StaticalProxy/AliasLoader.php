<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy;

use RuntimeException;
use Viserio\Component\Contracts\StaticalProxy\AliasLoader as AliasLoaderContract;
use Viserio\Component\StaticalProxy\Traits\ExistTrait;

class AliasLoader implements AliasLoaderContract
{
    use ExistTrait;

    /**
     * Flag to enable or disable real-time statical proxy.
     *
     * @var bool
     */
    protected $realTimeStaticalProxyActivated = false;

    /**
     * The namespace for all real-time statical proxies.
     *
     * @var string
     */
    protected $staticalProxyNamespace = 'StaticalProxy\\';

    /**
     * Path to the real-time statical proxies cache folder.
     *
     * @var string|null
     */
    private $cachePath;

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
     * @var array
     */
    private $resolving = [];

    /**
     * All cached resolved aliases.
     *
     * @var array
     */
    private static $cache = [];

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
     * Clone method.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * Set the cache path.
     *
     * @param string $path
     *
     * @return void
     */
    public function setCachePath(string $path): void
    {
        $this->cachePath = rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Get the cache path.
     *
     * @throws \RuntimeException If real-time statical proxy is active and no cache path is given
     *
     * @return string|null
     */
    public function getCachePath(): ?string
    {
        if ($this->realTimeStaticalProxyActivated === true && $this->cachePath === null) {
            throw new RuntimeException('Please provide a valid cache path.');
        }

        return $this->cachePath;
    }

    /**
     * Enable the real-time statical proxy.
     *
     * @return void
     */
    public function enableRealTimeStaticalProxy(): void
    {
        $this->realTimeStaticalProxyActivated = true;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $alias): bool
    {
        if ($this->realTimeStaticalProxyActivated === true &&
            mb_strpos($alias, $this->staticalProxyNamespace) === 0
        ) {
            $this->loadStaticalProxy($alias);

            return true;
        }

        // Skip recursive aliases if defined
        if (in_array($alias, $this->resolving)) {
            return false;
        }

        // Set it as the resolving class for when
        // we want to block recursive resolving
        $this->resolving[] = $alias;

        if (isset(self::$cache[$alias])) {
            // If we already have the alias in the cache don't bother resolving again
            $class = self::$cache[$alias];
        } elseif ($class = $this->resolveAlias($alias)) {
            // We've got a plain alias, now we can skip the others as this
            // is the most powerful one.
        } elseif ($class = $this->resolveNamespaceAlias($alias)) {
            // We've got a namespace alias, we can skip pattern matching.
        } elseif (! $class = $this->resolvePatternAlias($alias)) {
            // Lastly we'll try to resolve it through pattern matching. This is the most
            // expensive match type. Caching is recommended if you use this.
            return false;
        }

        // Remove the resolving class
        array_pop($this->resolving);

        if (! $this->exists($class)) {
            return false;
        }

        // Create the actual alias
        class_alias($class, $alias);

        if (! isset(self::$cache[$alias])) {
            self::$cache[$alias] = $class;
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

        $this->aliases[$classes] = self::$cache[$classes] = $alias;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAlias(): void
    {
        $class = func_get_args();

        foreach ($class as $alias) {
            if (isset($this->aliases[$alias])) {
                unset($this->aliases[$alias], self::$cache[$alias]);
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
    public function aliasPattern($patterns, string $translation = null): void
    {
        if (! is_array($patterns)) {
            $patterns = [$patterns => $translation];
        }

        foreach ($patterns as $patternKey => $resolver) {
            if (! $resolver instanceof Resolver) {
                $resolver = new Resolver($patternKey, $resolver);
            }

            $this->patterns[$patternKey] = $resolver;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAliasPattern(string $pattern, string $translation = null): void
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
    public function aliasNamespace(string $class, string $alias): void
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

            if (! $nsAlias || mb_strpos($alias, (string) $nsAlias) === 0) {
                if ($nsAlias) {
                    $alias = mb_substr($alias, mb_strlen($nsAlias) + 1);
                }

                $class             = $nsClass . '\\' . $alias;
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
    public function removeNamespaceAlias(): void
    {
        $class  = func_get_args();
        $filter = function ($namespace) use ($class) {
            return ! in_array($namespace[0], $class);
        };

        $this->namespaces = array_filter($this->namespaces, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        if (! $this->registered) {
            spl_autoload_register([$this, 'load'], true, true);

            $this->registered = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unregister(): void
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
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }

    /**
     * Set the real-time statical proxy namespace.
     *
     * @param string $namespace
     *
     * @return void
     */
    public function setStaticalProxyNamespace(string $namespace): void
    {
        $this->staticalProxyNamespace = rtrim($namespace, '\\') . '\\';
    }

    /**
     * Resolves pattern aliases.
     *
     * @param string $alias
     *
     * @return bool|string
     */
    protected function resolvePatternAlias(string $alias)
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

    /**
     * Load a real-time statical proxy for the given alias.
     *
     * @param string $alias
     *
     * @return void
     */
    protected function loadStaticalProxy(string $alias): void
    {
        require $this->ensureStaticalProxyExists($alias);
    }

    /**
     * Ensure that the given alias has an existing real-time statical proxy class.
     *
     * @param string $class
     * @param string $alias
     *
     * @return string
     */
    protected function ensureStaticalProxyExists(string $alias): string
    {
        $path = $this->getCachePath() . DIRECTORY_SEPARATOR . 'staticalproxy-' . sha1($alias) . '.php';

        if (file_exists($path)) {
            return $path;
        }

        file_put_contents(
            $path,
            $this->formatStaticalProxyStub(
                $alias,
                file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . 'StaticalProxy.stub')
            )
        );

        return $path;
    }

    /**
     * Format the statical proxy stub with the proper namespace and class.
     *
     * @param string $alias
     * @param string $stub
     *
     * @return string
     */
    protected function formatStaticalProxyStub(string $alias, string $stub): string
    {
        $replacements = [
            str_replace('/', '\\', dirname(str_replace('\\', '/', $alias))),
            self::getClassBasename($alias),
            mb_substr($alias, mb_strlen($this->staticalProxyNamespace)),
        ];

        return str_replace(
            ['DummyNamespace', 'DummyClass', 'DummyTarget'],
            $replacements,
            $stub
        );
    }

    /**
     * Get the class "basename" of the given object / class.
     *
     * @param string $class
     *
     * @return string
     */
    private static function getClassBasename(string $class): string
    {
        return basename(str_replace('\\', '/', $class));
    }
}
