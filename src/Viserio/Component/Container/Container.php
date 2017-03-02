<?php
declare(strict_types=1);
namespace Viserio\Component\Container;

use ArrayAccess;
use Closure;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use ReflectionClass;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Container\ContextualBindingBuilder as ContextualBindingBuilderContract;
use Viserio\Component\Contracts\Container\Exceptions\ContainerException;
use Viserio\Component\Contracts\Container\Exceptions\NotFoundException;
use Viserio\Component\Contracts\Container\Exceptions\UnresolvableDependencyException;
use Viserio\Component\Contracts\Container\Types as TypesContract;

class Container extends ContainerResolver implements ArrayAccess, ContainerContract, InvokerInterface, ContextualBindingBuilderContract
{
    /**
     * The container's bindings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * The container's extenders.
     *
     * @var array
     */
    protected $extenders = [];

    /**
     * Array full of container implementing the ContainerInterface.
     *
     * @var \Interop\Container\ContainerInterface[]
     */
    protected $delegates = [];

    /**
     * Array containing immutable instances.
     *
     * @var array
     */
    protected $immutable = [];

    /**
     * The normalized abstract instance.
     *
     * @var string
     */
    protected $abstract;

    /**
     * The concrete instance.
     *
     * @var string|array
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
     * Invoker instance.
     *
     * @var \Invoker\InvokerInterface|null
     */
    private $invoker;

    /**
     * Create a new container instance.
     */
    public function __construct()
    {
        // Auto-register the container
        $this->instance(Container::class, $this);
        $this->instance(ContainerContract::class, $this);
        $this->instance(ContainerInterface::class, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function bind($abstract, $concrete = null)
    {
        $concrete = ($concrete) ? $concrete : $abstract;

        if (is_array($abstract)) {
            $this->bindService(key($abstract), $concrete);
            $this->alias(key($abstract), current($abstract));
        } else {
            $this->bindService($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function bindIf(string $abstract, $concrete = null)
    {
        if (! $this->has($abstract)) {
            $this->bind($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function instance(string $abstract, $instance)
    {
        $this->bindPlain($abstract, $instance);
    }

    /**
     * {@inheritdoc}
     */
    public function singleton(string $abstract, $concrete = null)
    {
        $concrete = ($concrete) ? $concrete : $abstract;

        $this->bindSingleton($abstract, $concrete);
    }

    /**
     * {@inheritdoc}
     */
    public function alias(string $abstract, string $alias)
    {
        $this->bindings[$alias] = &$this->bindings[$abstract];
    }

    /**
     * {@inheritdoc}
     */
    public function make(string $abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function extend(string $abstract, Closure $closure)
    {
        $this->extendAbstract($abstract, $closure);
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
    public function forget(string $abstract)
    {
        unset($this->bindings[$abstract]);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($abstract, array $parameters = [])
    {
        if (is_string($abstract) && isset($this->contextualParameters[$abstract])) {
            $contextualParameters = $this->contextualParameters[$abstract];

            foreach ($contextualParameters as $key => $value) {
                if ($value instanceof Closure) {
                    $contextualParameters[$key] = $value($this);
                }
            }

            $parameters = array_replace($contextualParameters, $parameters);
        }

        if ($this->has($abstract)) {
            return $this->resolveBound($abstract, $parameters);
        }

        return $this->resolveNonBound($abstract, $parameters);
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
            return $binding[TypesContract::VALUE];
        }

        if ($concrete instanceof Closure) {
            array_unshift($parameters, $this);
        }

        if ($bindingType === TypesContract::PLAIN) {
            $resolved = $this->resolvePlain($abstract);
        } elseif ($bindingType === TypesContract::SERVICE) {
            $resolved = $this->resolveService($abstract, $parameters);
        } else {
            $resolved = $this->resolveSingleton($abstract, $parameters);
        }

        if (is_string($abstract)) {
            $this->extendResolved($abstract, $resolved);
        }

        return $resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveNonBound($abstract, array $parameters = [])
    {
        if ($abstract instanceof Closure) {
            array_unshift($parameters, $this);
        }

        if (is_string($abstract) && mb_strpos($abstract, '::')) {
            $parts    = explode('::', $abstract, 2);
            $abstract = [$this->resolve($parts[0]), $parts[1]];
        }

        $resolved = parent::resolve($abstract, $parameters);

        if (is_string($abstract)) {
            $this->extendResolved($abstract, $resolved);
        }

        return $resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function when(string $concrete): ContainerContract
    {
        $this->abstract = $concrete;

        if (isset($this->bindings[$this->abstract])) {
            $this->concrete = $this->bindings[$this->abstract][TypesContract::VALUE];
        } elseif (mb_strpos($this->abstract, '::')) {
            $this->concrete = explode('::', $this->abstract, 2);
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
            $this->parameter = mb_substr($this->parameter, 1);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function give($implementation)
    {
        if (! ($reflector = $this->getReflector($this->concrete))) {
            throw new UnresolvableDependencyException(sprintf('[%s] is not resolvable.', $this->concrete));
        }

        if ($reflector instanceof ReflectionClass && ! ($reflector = $reflector->getConstructor())) {
            throw new UnresolvableDependencyException(sprintf('[%s] must have a constructor.', $this->concrete));
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

        throw new UnresolvableDependencyException(sprintf(
            'Parameter [%s] cannot be injected in [%s].',
            is_object($this->parameter) ? get_class($this->parameter) : gettype($this->parameter),
            is_object($this->concrete) ? get_class($this->concrete) : gettype($this->concrete)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (! is_string($id)) {
            throw new ContainerException(sprintf(
                'The id parameter must be of type string, [%s] given.',
                is_object($id) ? get_class($id) : gettype($id)
            ));
        }

        if (isset($this->bindings[$id])) {
            return $this->resolve($id);
        } elseif ($resolved = $this->getFromDelegate($id)) {
            return $resolved;
        }

        throw new NotFoundException(
            sprintf('Abstract [%s] is not being managed by the container.', $id)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        if (! is_string($id)) {
            throw new ContainerException(sprintf(
                'The name parameter must be of type string, [%s] given.',
                is_object($id) ? get_class($id) : gettype($id)
            ));
        }

        $abstract = $id;

        if (isset($this->bindings[$abstract])) {
            return true;
        }

        return $this->hasInDelegate($id);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function call($callable, array $parameters = [])
    {
        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function register(ServiceProvider $provider, array $parameters = []): ContainerContract
    {
        $entries = $provider->getServices();

        foreach ($entries as $key => $callable) {
            if ($this->has($key)) {
                // Extend a previous entry
                $this->extend($key, function ($previous, ContainerInterface $container) use ($callable) {
                    $getPrevious = function () use ($previous) {
                        return $previous;
                    };

                    return $callable($container, $getPrevious);
                });
            } else {
                $this->singleton($key, function (ContainerInterface $container) use ($callable) {
                    return $callable($container, null);
                });
            }
        }

        foreach ($parameters as $key => $value) {
            $this->instance($key, $value);
        }

        return $this;
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
     *
     * @codeCoverageIgnore
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_string($value)) {
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
    public function offsetUnset($offset)
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
    public function offsetExists($offset)
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
     */
    protected function bindPlain(string $abstract, $concrete)
    {
        $this->bindings[$abstract] = [
            TypesContract::VALUE        => $concrete,
            TypesContract::IS_RESOLVED  => false,
            TypesContract::BINDING_TYPE => TypesContract::PLAIN,
        ];
    }

    /**
     * Bind a value which need to be resolved each time.
     *
     * @param string $abstract
     * @param mixed  $concrete
     */
    protected function bindService(string $abstract, $concrete)
    {
        $this->bindings[$abstract] = [
            TypesContract::VALUE        => $concrete,
            TypesContract::IS_RESOLVED  => false,
            TypesContract::BINDING_TYPE => TypesContract::SERVICE,
        ];
    }

    /**
     * Bind a value which need to be resolved one time.
     *
     * @param string $abstract
     * @param mixed  $concrete
     */
    protected function bindSingleton(string $abstract, $concrete)
    {
        $this->bindings[$abstract] = [
            TypesContract::VALUE        => $concrete,
            TypesContract::IS_RESOLVED  => false,
            TypesContract::BINDING_TYPE => TypesContract::SINGLETON,
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
     *
     * @codeCoverageIgnore
     */
    protected function getInvoker(): InvokerInterface
    {
        if (! $this->invoker) {
            $parameterResolver = [
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new DefaultValueResolver(),
                new TypeHintContainerResolver($this),
                new ParameterNameContainerResolver($this),
            ];

            $this->invoker = new Invoker(new ResolverChain($parameterResolver), $this);
        }

        return $this->invoker;
    }

    /**
     * Extend a resolved subject.
     *
     * @param string $abstract
     * @param mixed  &$resolved
     */
    protected function extendResolved($abstract, &$resolved)
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
     * "Extend" an abstract type in the container.
     *
     * @param string   $abstract
     * @param \Closure $closure
     */
    protected function extendAbstract(string $abstract, Closure $closure)
    {
        $this->extenders[$abstract][] = $closure;
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
     * @param \Closure|string|object $implementation
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
            return $container->make($implementation);
        };
    }
}
