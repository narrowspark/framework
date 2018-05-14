<?php
declare(strict_types=1);
namespace Viserio\Component\Container;

use Viserio\Component\Container\Compiler\Container\CompiledContainer;
use Viserio\Component\Container\Definition\AliasDefinition;
use Viserio\Component\Container\Dumper\PhpDumper;
use Viserio\Component\Contract\Container\Exception\LogicException;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\Container\TaggableServiceProvider as TaggableServiceProviderContract;
use Viserio\Component\Contract\Container\TaggedContainer as TaggedContainerContract;

class ContainerBuilder extends Container
{
    /**
     * The registered type aliases.
     *
     * @var \Viserio\Component\Container\Definition\AliasDefinition[]
     */
    protected $aliases = [];

    /**
     * Name of the container class, used to create the container.
     *
     * @var string
     */
    private $containerClass = 'CompiledContainer';

    /**
     * Name of the container parent class, used on compiled container.
     *
     * @var string
     */
    private $containerParentClass = CompiledContainer::class;

    /**
     * Namespace of the container class, used on compiled container.
     *
     * @var string
     */
    private $containerNamespace = 'Viserio\Component\Container';

    /**
     * Path to the compile dir.
     *
     * @var null|string
     */
    private $compileToDirectory;

    /**
     * Whether the container has already been built.
     *
     * @var bool
     */
    private $locked = false;

    /**
     * Gets all defined aliases.
     *
     * @return \Viserio\Component\Container\Definition\AliasDefinition[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Registers a service provider.
     *
     * @param \Viserio\Component\Contract\Container\ServiceProvider $provider   the service provider to register
     * @param array                                                 $parameters An array of values that customizes the provider
     *
     * @return void
     */
    public function register(ServiceProviderContract $provider, array $parameters = []): void
    {
        foreach ($provider->getFactories() as $key => $callable) {
            $this->singleton($key, function ($container) use ($callable) {
                return $callable($container, null);
            });
        }

        foreach ($provider->getExtensions() as $key => $callable) {
            if ($this->has($key)) {
                $this->extend($key, function ($container, $previous) use ($callable) {
                    return $callable($container, $previous);
                });
            } else {
                $this->singleton($key, function ($container) use ($callable) {
                    return $callable($container, null);
                });
            }
        }

        foreach ($parameters as $key => $value) {
            $this->instance($key, $value);
        }

        if ($provider instanceof TaggableServiceProviderContract) {
            foreach ($provider->getTags() as $tag => $bindings) {
                $this->tag($tag, $bindings);
            }
        }
    }

    /**
     * Alias a type to a different name.
     *
     * @param string $abstract
     * @param string $alias
     *
     * @return void
     */
    public function setAlias(string $abstract, string $alias): void
    {
        $definition = $this->definitions[$this->hasAlias($abstract) ? $this->aliases[$abstract]->getName() : $abstract] ?? null;

        if ($definition === null) {
            throw new LogicException('Alias cant be set to a undefined definition.');
        }

        if (isset($this->aliases[$alias])) {
            if ($this->aliases[$abstract] === $abstract) {
                throw new LogicException(\sprintf('[%s] is aliased to itself.', $abstract));
            }

            throw new LogicException(\sprintf('Alias cant be set a second time, for [%s]', $abstract));
        }

        if ($definition->getName() !== $alias) {
            $this->aliases[$alias] = new AliasDefinition($alias, $definition->getName());
        }
    }

    /**
     * Returns true if an alias exists under the given identifier.
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasAlias(string $id): bool
    {
        return isset($this->aliases[$id]);
    }

    /**
     * Compile the container for optimum performances.
     *
     * Be aware that the container is compiled once and never updated!
     *
     * Therefore:
     * - in production you should clear that directory every time you deploy
     * - in development you should not compile the container
     *
     * @param string      $cacheDirectory       Directory in which to put the compiled container
     * @param string      $containerClass       Name of the compiled class; Customize only if necessary
     * @param string      $containerParentClass Name of the compiled container parent class; Customize only if necessary
     * @param null|string $containerNamespace   Namespace of the compiled container, use null if you don't need a namespace
     *
     * @return \Viserio\Component\Container\ContainerBuilder
     */
    public function enableCompilation(
        string $cacheDirectory,
        string $containerClass       = 'CompiledContainer',
        string $containerParentClass = CompiledContainer::class,
        ?string $containerNamespace  = 'Viserio\Component\Container'
    ): self {
        $this->ensureNotLocked();

        $this->compileToDirectory   = $cacheDirectory;
        $this->containerClass       = $containerClass;
        $this->containerParentClass = $containerParentClass;
        $this->containerNamespace   = $containerNamespace;

        return $this;
    }

    /**
     * Build and return a container.
     *
     * @return \Viserio\Component\Contract\Container\TaggedContainer
     */
    public function build(): TaggedContainerContract
    {
        $this->locked  = true;
        $className     = \ltrim($this->containerClass, '\\');
        $namespace     = $this->containerNamespace !== null ? ('\\' . \ltrim(\rtrim($this->containerNamespace, '\\'), '\\')) : '';
        $fullClassName = $namespace . '\\' . $className;

        if ($this->compileToDirectory !== null) {
            if (! @\mkdir($this->compileToDirectory) && ! \is_dir($this->compileToDirectory)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $this->compileToDirectory));
            }

            $compiler              = new PhpDumper($this->containerClass, $this->containerParentClass, $this->containerNamespace);
            $compiledContainerFile = $compiler->compile($this->compileToDirectory, $this);

            // Only load the file if it hasn't been already loaded
            // (the container can be created multiple times in the same process)
            if (! \class_exists($fullClassName, false)) {
                require $compiledContainerFile;
            }

            return new $fullClassName();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAlias(string $id): string
    {
        if (! isset($this->aliases[$id])) {
            return $id;
        }

        /** @var AliasDefinition $aliasDefinition */
        $aliasDefinition = $this->aliases[$id];

        if ($aliasDefinition->isDeprecated()) {
            @\trigger_error($aliasDefinition->getDeprecationMessage(), \E_USER_DEPRECATED);
        }

        return $this->aliases[$id]->getName();
    }

    /**
     * Check if the container was build.
     *
     * @throws \LogicException
     */
    private function ensureNotLocked(): void
    {
        if ($this->locked) {
            throw new \LogicException('The PhpDumper cannot be modified after the container has been built.');
        }
    }
}
