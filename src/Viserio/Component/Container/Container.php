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
use Viserio\Component\Container\Definition\DefinitionHelper;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\LazyProxy\Instantiator\RealServiceInstantiator;
use Viserio\Component\Container\Reflection\ReflectionFactory;
use Viserio\Component\Container\Reflection\ReflectionResolver;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Container\Exception\BindingResolutionException;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Container\Exception\NotFoundException;
use Viserio\Component\Contract\Container\Factory as FactoryContract;
use Viserio\Component\Contract\Container\LazyProxy\Instantiator as InstantiatorContract;
use Viserio\Component\Contract\Container\TaggedContainer as TaggedContainerContract;
use Viserio\Component\Contract\Container\Types as TypesContract;

class Container extends ReflectionResolver implements TaggedContainerContract, InvokerInterface
{
    /**
     * The container's definitions.
     *
     * @var \Viserio\Component\Contract\Container\Compiler\Definition[]|\Viserio\Component\Contract\Container\Compiler\DeprecatedDefinition[]
     */
    protected $definitions = [];

    /**
     * The container's shared services.
     *
     * @var array
     */
    protected $services = [];

    /**
     * The registered type aliases.
     *
     * @var string[]
     */
    protected $aliases = [];

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
     * The extension closures for services.
     *
     * @var array
     */
    protected $extenders = [];

    /**
     * A InvokerInterface implementation.
     *
     * @var null|\Invoker\InvokerInterface
     */
    protected $invoker;

    /**
     * @var null|\Viserio\Component\Contract\Container\LazyProxy\Instantiator
     */
    private $proxyInstantiator;

    /**
     * Create a new container instance.
     */
    public function __construct()
    {
        // Auto-register the container
        $this->services = [
            __CLASS__                      => $this,
            ContainerContract::class       => $this,
            TaggedContainerContract::class => $this,
            ContainerInterface::class      => $this,
            FactoryContract::class         => $this,
        ];
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
     * {@inheritdoc}
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtenders(string $abstract): array
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->extenders[$abstract])) {
            return $this->extenders[$abstract];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setInstantiator(InstantiatorContract $proxyInstantiator): void
    {
        $this->proxyInstantiator = $proxyInstantiator;
    }

    /**
     * {@inheritdoc}
     */
    public function bind(string $abstract, $concrete = null): void
    {
        // Drop all of the stale instance and alias.
        unset($this->definitions[$abstract], $this->aliases[$abstract]);

        // If no concrete type was given, we will simply set the concrete type to the abstract type.
        $this->definitions[$abstract] = DefinitionHelper::create($abstract, $concrete ?? $abstract, TypesContract::SERVICE);
    }

    /**
     * {@inheritdoc}
     */
    public function instance(string $abstract, $instance): void
    {
        // Drop all of the stale instance, service and alias.
        unset($this->definitions[$abstract], $this->aliases[$abstract], $this->services[$abstract]);

        $this->definitions[$abstract] = DefinitionHelper::create($abstract, $instance, TypesContract::PLAIN);
    }

    /**
     * {@inheritdoc}
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        // If no concrete type was given, we will simply set the concrete type to the abstract type.
        $this->definitions[$abstract] = DefinitionHelper::create($abstract, $concrete ?? $abstract, TypesContract::SINGLETON);
    }

    /**
     * {@inheritdoc}
     */
    public function extend(string $abstract, Closure $closure): void
    {
        if (isset($this->definitions[$abstract])) {
            $this->definitions[$abstract]->addExtender($closure);
        } else {
            $this->extenders[$abstract][] = $closure;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function forgetExtenders(string $abstract): void
    {
        unset($this->extenders[$this->getAlias($abstract)]);
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
        unset($this->definitions[$abstract]);
    }

    /**
     * {@inheritdoc}
     */
    public function make($abstract, array $parameters = [])
    {
        if (\is_string($abstract)) {
            $abstract = $this->getAlias($abstract);

            if (isset($this->definitions[$abstract])) {
                $definition = $this->definitions[$abstract];

                if ($definition->isResolved() && $definition->isShared()) {
                    return $definition->getValue();
                }

                if (isset($this->extenders[$abstract])) {
                    foreach ($this->extenders[$abstract] as $extender) {
                        $definition->addExtender($extender);
                    }
                }

                $definition->resolve($this, $parameters);

                return $definition->getValue();
            }
        }

        if ($abstract instanceof Closure) {
            \array_unshift($parameters, $this);
        }

        return $this->resolve($abstract, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $this->ensureEntryIsString($id);
        $this->ensureEntryIsNotEmpty($id);

        $id = $this->getAlias($id);

        // If an instance of the type is currently being managed as a shared
        // we'll just return an existing instance.
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->definitions[$id])) {
            $definition = $this->definitions[$id];
            // Add
            if (isset($this->extenders[$id])) {
                foreach ($this->extenders[$id] as $extender) {
                    $definition->addExtender($extender);
                }
            }

            $definition->resolve($this);

            if ($definition->isDeprecated()) {
                @\trigger_error($definition->getDeprecationMessage(), \E_USER_DEPRECATED);
            }

            if ($definition->isShared()) {
                return $this->services[$id] = $definition->getValue();
            }

            return $definition->getValue();
        }

        if ($resolved = $this->getFromDelegate($id)) {
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
        $this->ensureEntryIsString($id);
        $this->ensureEntryIsNotEmpty($id);

        $id = $this->getAlias($id);

        if (isset($this->definitions[$id])) {
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
            throw new InvalidArgumentException('The tag name cant be a empty string.');
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
    public function setLazy(string $abstract): void
    {
        $definition = $this->definitions[$this->getAlias($abstract)];

        if ($definition instanceof ObjectDefinition) {
            $definition->setInstantiator($this->getProxyInstantiator());
        }

        $definition->setLazy(true);
    }

    /**
     * {@inheritdoc}
     */
    public function isLazy(string $abstract): bool
    {
        return $this->definitions[$this->getAlias($abstract)]->isLazy();
    }

    /**
     * Call the given function using the given parameters.
     *
     * @param callable|string $callable   function to call
     * @param array           $parameters parameters to use
     *
     * @throws \Invoker\Exception\InvocationException          base exception class for all the sub-exceptions below
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     *
     * @return mixed result of the function
     */
    public function call($callable, array $parameters = [])
    {
        if (is_method($callable)) {
            $callable = ReflectionFactory::parseStringMethod($callable);
        }

        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function isResolved(string $id): bool
    {
        $id = $this->getAlias($id);

        return isset($this->definitions[$id]) ? $this->definitions[$id]->isResolved() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->definitions = $this->services = $this->delegates = $this->aliases = [];
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
            $this->instance($offset, $value);
        } else {
            $this->bind($offset, $value);
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
        $this->forget($offset);
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
     * Get a proxy instantiator instance.
     *
     * @return \Viserio\Component\Contract\Container\LazyProxy\Instantiator
     */
    protected function getProxyInstantiator(): InstantiatorContract
    {
        if ($this->proxyInstantiator === null) {
            $this->proxyInstantiator = new RealServiceInstantiator();
        }

        return $this->proxyInstantiator;
    }

    /**
     * Get the alias for an id if available.
     *
     * @param string $id
     *
     * @return string
     */
    protected function getAlias(string $id): string
    {
        if (! isset($this->aliases[$id])) {
            return $id;
        }

        return $this->aliases[$id];
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
            /** @codeCoverageIgnoreStart */
            continue;
            /** @codeCoverageIgnoreEnd */
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveParameterClass(string $class): object
    {
        if ($this->has($class)) {
            return $this->get($class);
        }

        return $this->resolve($class);
    }

    /**
     * Resolve a non bound class.
     *
     * @param object|string $abstract
     * @param array         $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return object
     */
    protected function resolveClass($abstract, array $parameters = []): object
    {
        return $this->resolveReflectionClass(
            $reflectionClass = ReflectionFactory::getClassReflector($abstract),
            ReflectionFactory::getParameters($reflectionClass),
            $parameters
        );
    }

    /**
     * Resolve a method.
     *
     * @param array|string $method
     * @param array        $parameters
     *
     * @throws \ReflectionException
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return mixed
     */
    protected function resolveMethod($method, array $parameters = [])
    {
        return $this->resolveReflectionMethod(
            $reflectionMethod = ReflectionFactory::getMethodReflector($method),
            ReflectionFactory::getParameters($reflectionMethod),
            $parameters
        );
    }

    /**
     * Resolve a closure / function.
     *
     * @param \Closure|string $function
     * @param array           $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     * @throws \Viserio\Component\Contract\Container\Exception\RuntimeException
     *
     * @return mixed
     */
    protected function resolveFunction($function, array $parameters = [])
    {
        return $this->resolveReflectionFunction(
            $reflectionFunction = ReflectionFactory::getFunctionReflector($function),
            ReflectionFactory::getParameters($reflectionFunction),
            $parameters
        );
    }

    /**
     * Resolve a closure, function, method or a class.
     *
     * @param array|\Closure|object|string $abstract
     * @param array                        $parameters
     *
     * @throws \ReflectionException
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return mixed
     */
    protected function resolve($abstract, array $parameters = [])
    {
        if (! $abstract instanceof Closure && (is_invokable($abstract) || \is_object($abstract) || is_class($abstract))) {
            return $this->resolveClass($abstract, $parameters);
        }

        if (! is_function($abstract) && (\is_callable($abstract) || is_method($abstract))) {
            return $this->resolveMethod($abstract, $parameters);
        }

        if ($abstract instanceof Closure || is_function($abstract)) {
            return $this->resolveFunction($abstract, $parameters);
        }

        $buildStackString = \implode(', ', $this->buildStack);

        throw new BindingResolutionException(\sprintf(
            '[%s] is not resolvable.%s',
            $abstract,
            ($buildStackString !== '' ? ' Build stack: [' . $buildStackString . '].' : '')
        ));
    }

    /**
     * Check if the entry is a string.
     *
     * @param mixed $id
     */
    protected function ensureEntryIsString($id): void
    {
        if (! \is_string($id)) {
            throw new InvalidArgumentException(\sprintf(
                'The $id parameter must be of type string, [%s] given.',
                \is_object($id) ? \get_class($id) : \gettype($id)
            ));
        }
    }

    /**
     * Check if the entry is not empty.
     *
     * @param mixed $id
     *
     * @return void
     */
    protected function ensureEntryIsNotEmpty($id): void
    {
        if ($id === '') {
            throw new InvalidArgumentException('The $id parameter should not be a empty string.');
        }
    }
}
