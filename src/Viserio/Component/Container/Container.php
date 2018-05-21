<?php
declare(strict_types=1);
namespace Viserio\Component\Container;

use Closure;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Container\ContextualBindingBuilder as ContextualBindingBuilderContract;
use Viserio\Component\Contract\Container\Exception\ContainerException;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Container\Exception\NotFoundException;
use Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\Container\TaggableServiceProvider as TaggableServiceProviderContract;
use Viserio\Component\Contract\Container\TaggedContainer as TaggedContainerContract;
use Viserio\Component\Contract\Container\Types as TypesContract;

class Container extends ContainerResolver implements TaggedContainerContract, InvokerInterface, ContextualBindingBuilderContract
{
    /**
     * The container's bindings.
     *
     * @var array
     */
    protected $bindings;

    /**
     * The container's extenders.
     *
     * @var array
     */
    protected $extenders = [];

    /**
     * Array full of container implementing the ContainerInterface.
     *
     * @var \Psr\Container\ContainerInterface[]
     */
    protected $delegates = [];

    /**
     * All of the registered tags.
     *
     * @var array
     */
    protected $tags = [];

    /**
     * The normalized abstract instance.
     *
     * @var string
     */
    protected $abstract;

    /**
     * The concrete instance.
     *
     * @var mixed
     */
    protected $concrete;

    /**
     * Abstract target.
     *
     * @var string
     */
    protected $parameter;

    /**
     * Contextual parameters.
     *
     * @var array
     */
    protected $contextualParameters = [];

    /**
     * A InvokerInterface implementation.
     *
     * @var null|\Invoker\InvokerInterface
     */
    private $invoker;

    /**
     * Create a new container instance.
     *
     * @param array $bindings
     */
    public function __construct(array $bindings = [])
    {
        $this->bindings = $bindings;

        // Auto-register the container
//        $this->instance(Container::class, $this);
//        $this->instance(ContainerContract::class, $this);
//        $this->instance(TaggedContainerContract::class, $this);
//        $this->instance(ContainerInterface::class, $this);
//        $this->instance(FactoryContract::class, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function bind($abstract, $concrete = null): void
    {
        $concrete = $concrete ?? $abstract;

        if (\is_array($abstract)) {
            $this->bindService(\key($abstract), $concrete);
            $this->alias(\key($abstract), \current($abstract));
        } else {
            $this->bindService($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bindIf(string $abstract, $concrete = null): void
    {
        if (! $this->has($abstract)) {
            $this->bind($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function instance(string $abstract, $instance): void
    {
        $this->bindPlain($abstract, $instance);
    }

    /**
     * {@inheritdoc}
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $concrete = $concrete ?? $abstract;

        $this->bindSingleton($abstract, $concrete);
    }

    /**
     * {@inheritdoc}
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->bindings[$alias]                       = &$this->bindings[$abstract];
        $this->bindings[$alias][TypesContract::ALIAS] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function extend(string $abstract, Closure $closure): void
    {
        $this->extenders[$abstract][] = $closure;
    }

    /**
     * {@inheritdoc}
     */
    public function delegate(ContainerInterface $container): ContainerContract
    {
        $this->delegates[] = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasInDelegate(string $abstract): bool
    {
        foreach ($this->delegates as $container) {
            if ($container->has($abstract)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract]);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($subject, array $parameters = [])
    {
        if (\is_string($subject)) {
            if (isset($this->contextualParameters[$subject])) {
                $parameters = $this->contextualParameters[$subject];

                foreach ($parameters as $key => $value) {
                    if ($value instanceof Closure) {
                        $parameters[$key] = $value($this);
                    }
                }

                $parameters = \array_replace($parameters, $parameters);
            }

            if ($this->has($subject)) {
                return $this->resolveBound($subject, $parameters);
            }
        }

        return $this->resolveNonBound($subject, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveBound(string $abstract, array $parameters = [])
    {
        $binding     = $this->bindings[$abstract];
        $concrete    = $binding[TypesContract::VALUE];
        $bindingType = $binding[TypesContract::BINDING_TYPE];

        if ($this->isComputed($binding)) {
            return $concrete;
        }

        if ($concrete instanceof Closure) {
            \array_unshift($parameters, $this);
        }

        if ($bindingType === TypesContract::PLAIN) {
            $resolved = $this->resolvePlain($abstract);
        } elseif ($bindingType === TypesContract::SERVICE) {
            $resolved = $this->resolveService($abstract, $parameters);
        } else {
            $resolved = $this->resolveSingleton($abstract, $parameters);
        }

        $this->extendResolved($abstract, $resolved);

        return $resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveNonBound($abstract, array $parameters = [])
    {
        if ($abstract instanceof Closure) {
            \array_unshift($parameters, $this);
        }

        if (\is_string($abstract) && \mb_strpos($abstract, '@')) {
            $parts    = \explode('@', $abstract, 2);
            $abstract = [$this->resolve($parts[0]), $parts[1]];
        }

        $resolved = parent::resolve($abstract, $parameters);

        if (\is_string($abstract)) {
            $this->extendResolved($abstract, $resolved);
        }

        return $resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function when(string $concrete): ContextualBindingBuilderContract
    {
        $this->abstract = $concrete;

        if (isset($this->bindings[$this->abstract])) {
            $this->concrete = $this->bindings[$this->abstract][TypesContract::VALUE];
        } elseif (\mb_strpos($this->abstract, '@')) {
            $this->concrete = \explode('@', $this->abstract, 2);
        } else {
            $this->concrete = $this->abstract;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function needs(string $abstract): ContextualBindingBuilderContract
    {
        $this->parameter = $abstract;

        if ($this->parameter[0] === '$') {
            $this->parameter = \mb_substr($this->parameter, 1);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function give($implementation)
    {
        if (! ($reflector = $this->getReflector($this->concrete))) {
            throw new UnresolvableDependencyException(\sprintf('[%s] is not resolvable.', $this->concrete));
        }

        if ($reflector instanceof ReflectionClass && ! ($reflector = $reflector->getConstructor())) {
            throw new UnresolvableDependencyException(\sprintf('[%s] must have a constructor.', $this->concrete));
        }

        $reflectionParameters = $reflector->getParameters();
        $contextualParameters = &$this->contextualParameters[$this->abstract];

        foreach ($reflectionParameters as $key => $parameter) {
            $class = $parameter->getClass();

            if ($this->parameter === $parameter->name) {
                return $contextualParameters[$key] = $implementation;
            }

            if ($class && $this->parameter === $class->name) {
                return $contextualParameters[$key] = $this->contextualBindingFormat($implementation, $class);
            }
        }

        $concrete = \gettype($this->concrete);

        if (\is_object($this->concrete)) {
            $concrete = \get_class($this->concrete);
        } elseif (\is_array($this->concrete)) {
            $concrete = $this->concrete[0];
        } elseif (\is_string($this->concrete)) {
            $concrete = $this->concrete;
        }

        throw new UnresolvableDependencyException(\sprintf(
            'Parameter [%s] cannot be injected in [%s].',
            $this->parameter,
            $concrete
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (! \is_string($id)) {
            throw new ContainerException(\sprintf(
                'The id parameter must be of type string, [%s] given.',
                \is_object($id) ? \get_class($id) : \gettype($id)
            ));
        }

        if (isset($this->bindings[$id])) {
            return $this->resolve($id);
        }

        $resolved = $this->getFromDelegate($id);

        if ((bool) $resolved) {
            return $resolved;
        }

        throw new NotFoundException(
            \sprintf('Abstract [%s] is not being managed by the container.', $id)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        if (! \is_string($id)) {
            throw new ContainerException(\sprintf(
                'The id parameter must be of type string, [%s] given.',
                \is_object($id) ? \get_class($id) : \gettype($id)
            ));
        }

        if (isset($this->bindings[$id])) {
            return true;
        }

        return $this->hasInDelegate($id);
    }

    /**
     * {@inheritdoc}
     */
    public function tag(string $tagName, array $abstracts): void
    {
        if ($tagName === '') {
            throw new InvalidArgumentException('The tag name must be a non-empty string.');
        }

        if (! isset($this->tags[$tagName])) {
            $this->tags[$tagName] = [];
        }

        foreach ($abstracts as $abstract) {
            $this->tags[$tagName][] = $abstract;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTagged(string $tag): array
    {
        $results = [];

        if (isset($this->tags[$tag])) {
            foreach ($this->tags[$tag] as $abstract) {
                $results[] = $this->get($abstract);
            }
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function call($callable, array $parameters = [])
    {
        if (\is_string($callable) && \mb_strpos($callable, '@')) {
            $callable = \explode('@', $callable, 2);
        }

        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * {@inheritdoc}
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
                $this->extend($key, function ($previous, $container) use ($callable) {
                    // Extend a previous entry
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
     * {@inheritdoc}
     */
    public function isComputed($binding): bool
    {
        return $binding[TypesContract::IS_RESOLVED] && $binding[TypesContract::BINDING_TYPE] !== TypesContract::SERVICE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->bindings = $this->extenders = $this->delegates = $this->contextualParameters = $this->buildStack = [];
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value): void
    {
        if (\is_string($value)) {
            $this->bindPlain($offset, $value);
        } else {
            $this->bindService($offset, $value);
        }
    }

    /**
     * Get the value at a given offset.
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Unset the value at a given offset.
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->bindings[$offset]);
    }

    /**
     * Determine if a given offset exists.
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Attempt to get a service from the stack of delegated backup containers.
     *
     * @param string $abstract
     *
     * @return mixed
     */
    protected function getFromDelegate(string $abstract)
    {
        foreach ($this->delegates as $container) {
            if ($container->has($abstract)) {
                return $container->get($abstract);
            }
            // @codeCoverageIgnoreStart
            continue;
            // @codeCoverageIgnoreEnd
        }

        return false;
    }

    /**
     * Bind a plain value.
     *
     * @param string $abstract
     * @param mixed  $concrete
     *
     * @return void
     */
    protected function bindPlain(string $abstract, $concrete): void
    {
        $this->bindings[$abstract] = [
            TypesContract::VALUE        => $concrete,
            TypesContract::IS_RESOLVED  => false,
            TypesContract::BINDING_TYPE => TypesContract::PLAIN,
            TypesContract::ALIAS        => false,
        ];
    }

    /**
     * Bind a value which need to be resolved each time.
     *
     * @param string $abstract
     * @param mixed  $concrete
     *
     * @return void
     */
    protected function bindService(string $abstract, $concrete): void
    {
        $this->bindings[$abstract] = [
            TypesContract::VALUE        => $concrete,
            TypesContract::IS_RESOLVED  => false,
            TypesContract::BINDING_TYPE => TypesContract::SERVICE,
            TypesContract::ALIAS        => false,
        ];
    }

    /**
     * Bind a value which need to be resolved one time.
     *
     * @param string $abstract
     * @param mixed  $concrete
     *
     * @return void
     */
    protected function bindSingleton(string $abstract, $concrete): void
    {
        $this->bindings[$abstract] = [
            TypesContract::VALUE        => $concrete,
            TypesContract::IS_RESOLVED  => false,
            TypesContract::BINDING_TYPE => TypesContract::SINGLETON,
            TypesContract::ALIAS        => false,
        ];
    }

    /**
     * Resolve a plain value from the container.
     *
     * @param string $abstract
     *
     * @return mixed
     */
    protected function resolvePlain(string $abstract)
    {
        $binding                             = &$this->bindings[$abstract];
        $binding[TypesContract::IS_RESOLVED] = true;

        return $binding[TypesContract::VALUE];
    }

    /**
     * Resolve a service from the container.
     *
     * @param string $abstract
     * @param array  $parameters
     *
     * @return mixed
     */
    protected function resolveService(string $abstract, array $parameters = [])
    {
        $binding                             = &$this->bindings[$abstract];
        $binding[TypesContract::IS_RESOLVED] = true;

        return parent::resolve($binding[TypesContract::VALUE], $parameters);
    }

    /**
     * Resolve a singleton from the container.
     *
     * @param string $abstract
     * @param array  $parameters
     *
     * @return mixed
     */
    protected function resolveSingleton(string $abstract, array $parameters = [])
    {
        $binding = &$this->bindings[$abstract];

        $binding[TypesContract::VALUE]       = parent::resolve($binding[TypesContract::VALUE], $parameters);
        $binding[TypesContract::IS_RESOLVED] = true;

        return $binding[TypesContract::VALUE];
    }

    /**
     * Get a configured instance of invoker.
     *
     * @return \Invoker\InvokerInterface
     */
    protected function getInvoker(): InvokerInterface
    {
        if (! $this->invoker) {
            $parameterResolver = [
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new DefaultValueResolver(),
                new TypeHintContainerResolver($this),
            ];

            $this->invoker = new Invoker(new ResolverChain($parameterResolver), $this);
        }

        return $this->invoker;
    }

    /**
     * Extend a resolved subject.
     *
     * @param string $abstract
     * @param mixed  $resolved
     *
     * @return void
     */
    protected function extendResolved($abstract, &$resolved): void
    {
        if (! isset($this->extenders[$abstract])) {
            return;
        }

        $binding = $this->bindings[$abstract];

        foreach ($this->extenders[$abstract] as $extender) {
            $resolved = $this->extendConcrete($resolved, $extender);
        }

        if ($binding && $binding[TypesContract::BINDING_TYPE] !== TypesContract::SERVICE) {
            unset($this->extenders[$abstract]);

            $this->bindings[$abstract][TypesContract::VALUE] = $resolved;
        }
    }

    /**
     * Call the given closure.
     *
     * @param mixed    $concrete
     * @param \Closure $closure
     *
     * @return mixed
     */
    protected function extendConcrete($concrete, Closure $closure)
    {
        return $closure($concrete, $this);
    }

    /**
     * Format a class binding.
     *
     * @param \Closure|object|string $implementation
     * @param \ReflectionClass       $parameterClass
     *
     * @return \Closure|object
     */
    protected function contextualBindingFormat($implementation, ReflectionClass $parameterClass)
    {
        if ($implementation instanceof Closure || $implementation instanceof $parameterClass->name) {
            return $implementation;
        }

        return function (ContainerContract $container) use ($implementation) {
            return $container->resolve($implementation);
        };
    }
}
