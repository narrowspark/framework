<?php
declare(strict_types=1);
namespace Viserio\Container;

use ArrayAccess;
use Closure;
use ReflectionClass;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Viserio\Container\Proxy\ProxyFactory;
use Viserio\Container\Traits\NormalizeClassNameTrait;
use Viserio\Contracts\Container\Container as ContainerContract;
use Viserio\Contracts\Container\ContextualBindingBuilder as ContextualBindingBuilderContract;
use Viserio\Contracts\Container\Exceptions\NotFoundException;
use Viserio\Contracts\Container\Exceptions\UnresolvableDependencyException;
use Viserio\Contracts\Container\Types as TypesContract;

class Container extends ContainerResolver implements ArrayAccess, ContainerInterface, ContainerContract, InvokerInterface, ContextualBindingBuilderContract
{
    use NormalizeClassNameTrait;

    /**
     * The container's bindings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Invoker instance.
     *
     * @var \Invoker\InvokerInterface|null
     */
    private $invoker;

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
     * The concrete instance.
     *
     * @var string
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
     * ProxyFactory instance.
     *
     * @var \Viserio\Container\Proxy\ProxyFactory
     */
    private $proxyFactory;

    /**
     * Create a new container instance.
     *
     * @param string|null $proxyDirectory
     */
    public function __construct(string $proxyDirectory = null)
    {
        $writeProxiesToFile = $proxyDirectory === null ? false : true;
        $this->proxyFactory = new ProxyFactory($writeProxiesToFile, $proxyDirectory);

        // Auto-register the container
        $this->singleton(Container::class, $this);
        $this->singleton(ContainerContract::class, $this);
        $this->singleton(ContainerInteropInterface::class, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function bind($abstract, $concrete = null)
    {
        $abstract = $this->normalize($abstract);
        $concrete = ($concrete) ? $this->normalize($concrete) : $abstract;

        if (is_array($abstract)) {
            $this->bindService(key($abstract), $concrete);
            $this->alias(key($abstract), current($abstract));
        } else {
            $this->bindService($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bindIf(string $abstract, $concrete = null)
    {
        if (!$this->has($abstract)) {
            $this->bind($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function instance($abstract, $instance)
    {
        $abstract = $this->normalize($abstract);

        if (is_array($abstract)) {
            $this->bindPlain(key($abstract), $instance);
            $this->alias(key($abstract), current($abstract));
        } else {
            $this->bindPlain($abstract, $instance);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function singleton($abstract, $concrete = null)
    {
        $abstract = $this->normalize($abstract);
        $concrete = ($concrete) ? $this->normalize($concrete) : $abstract;

        if (is_array($abstract)) {
            $this->bindSingleton(key($abstract), $concrete);
            $this->alias(key($abstract), current($abstract));
        } else {
            $this->bindSingleton($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function alias(string $abstract, string $alias)
    {
        $alias = $this->normalize($alias);
        $abstract = $this->normalize($abstract);

        $this->bindings[$alias] = &$this->bindings[$abstract];
    }

    /**
     * {@inheritdoc}
     */
    public function make(string $abstract, array $parameters = [])
    {
        return $this->resolve($this->normalize($abstract), $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function extend(string $abstract, Closure $closure)
    {
        $this->extendAbstract($this->normalize($abstract), $closure);
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
        $binding = $this->bindings[$abstract];
        $concrete = $binding[TypesContract::VALUE];
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

        $this->extendResolved($abstract, $resolved);

        return $resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveNonBound(string $concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            array_unshift($parameters, $this);
        }

        if (is_string($concrete) && strpos($concrete, '::')) {
            $parts = explode('::', $concrete, 2);
            $concrete = [$this->resolve($parts[0]), $parts[1]];
        }

        $resolved = parent::resolve($concrete, $parameters);

        $this->extendResolved($concrete, $resolved);

        return $resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function when(string $concrete): ContainerContract
    {
        $this->abstract = $this->normalize($concrete);

        if (isset($this->bindings[$this->abstract])) {
            $this->concrete = $this->bindings[$this->abstract][TypesContract::VALUE];
        } elseif (strpos($this->abstract, '::')) {
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
        $this->parameter = $this->normalize($abstract);

        if ($this->parameter[0] === '$') {
            $this->parameter = substr($this->parameter, 1);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function give($implementation)
    {
        if (!($reflector = $this->getReflector($this->concrete))) {
            throw new UnresolvableDependencyException("[$this->concrete] is not resolvable.");
        }

        if ($reflector instanceof ReflectionClass && !($reflector = $reflector->getConstructor())) {
            throw new UnresolvableDependencyException("[$this->concrete] must have a constructor.");
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

        throw new UnresolvableDependencyException("Parameter [$this->parameter] cannot be injected in [$this->concrete].");
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @param string $name Entry name or a class name.
     *
     * @throws InvalidArgumentException The name parameter must be of type string.
     * @throws DependencyException      Error while resolving the entry.
     * @throws NotFoundException        No entry found for the given name.
     *
     * @return mixed
     */
    public function get($id)
    {
        if (! is_string($id)) {
            throw new InvalidArgumentException(sprintf(
                'The id parameter must be of type string, %s given',
                is_object($id) ? get_class($id) : gettype($id)
            ));
        }

        if (isset($this->bindings[$id])) {
            return $this->resolve($id);
        }

        if ($resolved = $this->getFromDelegate($id)) {
            return $resolved;
        }

        throw new NotFoundException(
            sprintf('Abstract (%s) is not being managed by the container', $id)
        );
    }

    /**
     * Test if the container can provide something for the given name.
     *
     * @param string $name Entry name or a class name.
     *
     * @throws InvalidArgumentException The name parameter must be of type string.
     *
     * @return bool
     */
    public function has($id)
    {
        if (! is_string($id)) {
            throw new InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($id) ? get_class($id) : gettype($id)
            ));
        }

        $abstract = $this->normalize($id);

        if (isset($this->bindings[$abstract])) {
            return true;
        }

        return $this->hasInDelegate($id);
    }

    /**
     * {@inheritdoc}
     */
    public function call($callable, array $parameters = [])
    {
        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * Check if a binding is computed.
     *
     * @param array $binding
     *
     * @return bool
     */
    public static function isComputed($binding): bool
    {
        return $binding[TypesContract::IS_RESOLVED] && $binding[TypesContract::BINDING_TYPE] !== TypesContract::SERVICE;
    }

    /**
     * Set the value at a given offset
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
     * Get the value at a given offset
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
     * Unset the value at a given offset
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->bindings[$offset]);
    }

    /**
     * Determine if a given offset exists
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
     * @param array  $args
     *
     * @return mixed
     */
    protected function getFromDelegate(string $abstract)
    {
        foreach ($this->delegates as $container) {
            if ($container->has($abstract)) {
                return $container->get($abstract);
            }

            continue;
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
            TypesContract::VALUE => $concrete,
            TypesContract::IS_RESOLVED => false,
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
            TypesContract::VALUE => $concrete,
            TypesContract::IS_RESOLVED => false,
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
            TypesContract::VALUE => $concrete,
            TypesContract::IS_RESOLVED => false,
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
        $binding = &$this->bindings[$abstract];
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
        $binding = &$this->bindings[$abstract];
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

        if ($binding[TypesContract::IS_RESOLVED]) {
            return $binding[TypesContract::VALUE];
        }

        $binding[TypesContract::VALUE] = parent::resolve($binding[TypesContract::VALUE], $parameters);
        $binding[TypesContract::IS_RESOLVED] = true;

        return $binding[TypesContract::VALUE];
    }

    /**
     * Check if class is immutable.
     *
     * @param string $concrete
     *
     * @throws ContainerException
     */
    protected function notImmutable(string $concrete)
    {
        if (isset($this->immutable[$concrete])) {
            throw new ContainerException(sprintf('Attempted overwrite of initialized component [%s]', $concrete));
        }
    }

    /**
     * Get a configured instance of invoker.
     *
     * @return \Invoker\InvokerInterface
     */
    protected function getInvoker(): InvokerInterface
    {
        if (! $this->invoker) {
            $parameterResolver = new ResolverChain([
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new DefaultValueResolver(),
                new TypeHintContainerResolver($this),
                new ParameterNameContainerResolver($this),
            ]);

            $this->invoker = new Invoker($parameterResolver, $this);
        }

        return $this->invoker;
    }

    /**
     * Extend a resolved subject
     *
     * @param string $abstract
     * @param mixed  &$resolved
     */
    protected function extendResolved($abstract, &$resolved)
    {
        if (!is_string($abstract) || !isset($this->extenders[$abstract])) {
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
     * Call the given closure
     *
     * @param mixed   $concrete
     * @param \Closure $closure
     *
     * @return mixed
     */
    protected function extendConcrete($concrete, Closure $closure)
    {
        return $closure($concrete, $this);
    }

    /**
     * Format a class binding
     *
     * @param string|closure|object $implementation
     * @param \ReflectionClass      $parameterClass
     *
     * @return \Closure|object
     */
    protected function contextualBindingFormat($implementation, ReflectionClass $parameter)
    {
        if ($implementation instanceof Closure || $implementation instanceof $parameter->name) {
            return $implementation;
        }

        return function ($container) use ($implementation) {
            return $container->make($implementation);
        };
    }
}
