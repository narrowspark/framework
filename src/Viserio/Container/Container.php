<?php
namespace Viserio\Container;

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

use ArrayAccess;
use Closure;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Viserio\Container\Exception\BindingResolutionException;
use Viserio\Container\Exception\ContainerException;
use Viserio\Container\Exception\NotFoundException;
use Viserio\Container\Traits\ContainerArrayAccessTrait;
use Viserio\Container\Traits\ContainerResolverTraits;
use Viserio\Container\Traits\MockerContainerTrait;
use Viserio\Container\Traits\DelegateTrait;
use Viserio\Container\Traits\ServiceProviderTrait;
use Viserio\Contracts\Container\Container as ContainerContract;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use InvalidArgumentException;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Serializable;
use Opis\Closure\SerializableClosure;

/**
 * Container.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Container implements ArrayAccess, Serializable, ContainerInteropInterface, ContainerContract
{
    /**
     * Array Access Support
     * Mock Support
     * Resolver
     * Defining Sub/Nested Containers
     * ServiceProvider Support
     */
    use ContainerArrayAccessTrait,
    MockerContainerTrait,
    ContainerResolverTraits,
    DelegateTrait,
    ServiceProviderTrait;

    /**
     * The registered type aliases.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * Array containing every binding in the container.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Array containing every singleton in the container.
     *
     * @var array
     */
    protected $singletons = [];

    /**
     * Array containing immutable instances.
     *
     * @var array
     */
    protected $immutable = [];

    /**
     * Array containing every non-object binding.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Array containing every key.
     *
     * @var array
     */
    protected $keys = [];

    /**
     * The contextual binding map.
     *
     * @var array
     */
    public $contextual = [];

    /**
     * The stack of concretions being current built.
     *
     * @var array
     */
    protected $buildStack = [];

    /**
     * Invoker instance.
     *
     * @var \Invoker\InvokerInterface|null
     */
    protected $invoker;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->singleton('Viserio\Container\Container', $this);
        $this->singleton(ContainerContract::class, $this);
        $this->singleton(ContainerInteropInterface::class, $this);
    }

    /**
     * Alias a type to a different name.
     *
     * @param string $alias
     * @param string $abstract
     */
    public function alias($alias, $abstract)
    {
        $this->keys[$alias] =
        $this->keys[$abstract] = true;
        $this->aliases[$alias] = $abstract;
    }

    /**
     * {@inheritdoc}
     */
    public function singleton($alias, $concrete = null)
    {
        return $this->bind($alias, $concrete, true);
    }

    /**
     * {@inheritdoc}
     */
    public function bind($alias, $concrete = null, $singleton = false)
    {
        $alias    = $this->normalize($alias);
        $concrete = $this->normalize($concrete);

        $this->notImmutable($alias);

        // If the given types are actually an array, we will assume an alias is being
        // defined and will grab this "real" abstract class name and register this
        // alias with the container so that it can be used as a shortcut for it.
        if (is_array($alias)) {
            list($alias, $abstract) = $this->extractAlias($alias);
            $this->alias($alias, $abstract);
        }

        if (!is_object($alias)) {
            $this->keys[$alias] = true;
        }

        // If the given type is actually an string, we will register this value
        // with the container so that it can be used.
        if ($this->isString($alias, $concrete)) {
            $this->values[$alias] = $concrete;

            return $concrete;
        }

        // If no concrete type was given, we will simply set the concrete type to the
        // abstract type. This will allow concrete type to be registered as shared
        // without being forced to state their classes in both of the parameter.
        $this->dropStaleSingletons($alias);

        if (null === $concrete) {
            $concrete = $alias;
        }

        $this->bindings[$alias] = compact('concrete', 'singleton');

        return $this->bindings[$alias]['concrete'];
    }

    /**
     * {@inheritdoc}
     */
    public function make($abstract, array $parameters = [])
    {
        if (!is_string($abstract)) {
            throw new InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($abstract) ? get_class($abstract) : gettype($abstract)
            ));
        }

        $alias = $this->getAlias($this->normalize($abstract));

        if ($this->bound($alias)) {
            try {
                // If an instance of the type is currently being managed as a singleton we'll
                // just return an existing instance instead of instantiating new instances
                // so the developer can keep using the same objects instance every time.
                if (isset($this->singletons[$alias])) {
                    $this->immutable[$alias] = true;

                    return $this->singletons[$alias];
                }

                if (isset($this->values[$alias])) {
                    $this->immutable[$alias] = true;

                    return $this->values[$alias];
                }

                $concrete = $this->getConcrete($alias);

                if ($this->isBuildable($concrete, $alias)) {
                    $object = $this->build($concrete, $parameters);
                } else {
                    $object = $this->make($concrete, $parameters);
                }

                // If the requested type is registered as a singleton we'll want to cache off
                // the instances in "memory" so we can return it later without creating an
                // entirely new instance of an object on each subsequent request for it.
                if ($this->isSingleton($alias)) {
                    $this->singletons[$alias] = $object;
                }

                $this->immutable[$alias] = true;

                return $object;
            } catch (\Exception $prev) {
                throw new ContainerException("An error occured while fetching entry '".$id."'", 0, $prev);
            }
        } else {
            throw new NotFoundException(sprintf('No entry was found for this identifier [%s].', $alias));
        }
    }

    /**
     * Build a concrete instance of a class.
     *
     * @param string $concrete The name of the class to buld.
     * @param array  $args
     *
     * @throws BindingResolutionException
     *
     * @return mixed The instantiated class.
     */
    public function build($concrete, array $args = [])
    {
        // If the concrete type is actually a Closure, we will just execute it and
        // hand back the results of the functions, which allows functions to be
        // used as resolvers for more fine-tuned resolution of these objects.
        if ($concrete instanceof Closure) {
            return $concrete($this, $args);
        }

        $instances = $this->reflect($concrete, $args);

        return $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function when($concrete)
    {
        $builder = new ContextualBindingBuilder($this->normalize($concrete));
        $builder->setContainer($this);

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function isRegistered($alias)
    {
        return isset($this->keys[$this->normalize($alias)]);
    }

    /**
     * Determine if a given string is an alias.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isAlias($name)
    {
        return isset($this->aliases[$this->normalize($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleton($alias)
    {
        $alias = $this->normalize($alias);

        if (isset($this->bindings[$alias]['singleton'])) {
            $singleton = $this->bindings[$alias]['singleton'];
        } else {
            $singleton = false;
        }

        return (isset($this->singletons[$alias]) || $singleton === true);
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function bound($alias)
    {
        $alias = $this->normalize($alias);

        return (
            isset($this->bindings[$alias]) ||
            $this->isSingleton($alias) ||
            $this->isAlias($alias) ||
            isset($this->values[$alias])
        );
    }

    /**
     * Call the given Closure and inject its dependencies.
     *
     * @param callable $callable
     * @param array    $parameters
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function call($callable, array $parameters = [])
    {
        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * Extend an existing binding.
     *
     * @param string   $binding The name of the binding to extend.
     * @param \Closure $closure The function to use to extend the existing binding.
     *
     * @throws ContainerException
     */
    public function extend($binding, \Closure $closure)
    {
        $boundObject = $this->getRaw($binding);

        if ($boundObject === null) {
            throw new ContainerException(
                sprintf('Cannot extend %s because it has not yet been bound.', $binding)
            );
        }

        $binding = $this->normalize($binding);

        $this->bind($binding, function ($container) use ($closure, $boundObject) {
            return $closure($container, $boundObject($container));
        });
    }

    /**
     * Get the raw object prior to resolution.
     *
     * @param string $binding The $binding key to get the raw value from.
     *
     * @return string Value of the $binding.
     */
    public function getRaw($binding)
    {
        $binding = $this->normalize($binding);

        if (isset($this->bindings[$binding])) {
            return $this->bindings[$binding]['concrete'];
        }

        return;
    }

    /**
     * Get the container's bindings.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Get the container's values.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Get the container's values.
     *
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * Add a contextual binding to the container.
     *
     * @param string          $concrete
     * @param string          $alias
     * @param \Closure|string $implementation
     */
    public function addContextualBinding($concrete, $alias, $implementation)
    {
        $this->contextual[$this->normalize($concrete)][$this->normalize($alias)] = $this->normalize($implementation);
    }

    /**
     * Invokes the 'make' method
     *
     * @param string $abstract   Abstract type name
     * @param array  $arguments  (optional) Arguments that will be passed to the constructor
     *
     * @return mixed
     */

    public function __invoke($abstract, array $arguments = array())
    {
        return $this->make($abstract, $arguments);
    }

    /**
     * Serialize the container
     *
     * @access  public
     *
     * @return  string
     */
    public function serialize()
    {
        SerializableClosure::enterContext();

        $object = serialize(array(
            'bindings' => $this->bindings,
            'aliases'  => $this->aliases,
        ));

        SerializableClosure::exitContext();

        return $object;
    }

    /**
     * Deserialize the container
     *
     * @access  public
     *
     * @param   string  Serialized data
     */
    public function unserialize($data)
    {
        $object = SerializableClosure::unserializeData($data);
        $this->bindings = $object['bindings'];
        $this->aliases  = $object['aliases'];
    }

    /**
     * Get the alias for an abstract if available.
     *
     * @param string $alias
     *
     * @return string
     */
    protected function getAlias($alias)
    {
        $alias = $this->normalize($alias);

        return isset($this->aliases[$alias]) ? $this->aliases[$alias] : $alias;
    }

    /**
     * Get the contextual concrete binding for the given abstract.
     *
     * @param string $alias
     *
     * @return string
     */
    protected function getContextualConcrete($alias)
    {
        $alias = $this->normalize($alias);

        if (isset($this->contextual[end($this->buildStack)][$alias])) {
            return $this->contextual[end($this->buildStack)][$alias];
        }
    }

    /**
     * Get the concrete type for a given abstract.
     *
     * @param string $alias
     *
     * @return mixed $concrete
     */
    protected function getConcrete($alias)
    {
        if (null !== ($concrete = $this->getContextualConcrete($alias))) {
            return $concrete;
        }

        $alias = $this->normalize($alias);

        // If we don't have a registered resolver or concrete for the type, we'll just
        // assume each type is a concrete name and will attempt to resolve it as is
        // since the container should be able to resolve concretes automatically.
        if (!isset($this->bindings[$alias])) {
            if (isset($this->bindings[$this->normalize($alias)])) {
                $alias = $this->normalize($alias);
            }

            return $alias;
        }

        return $this->bindings[$alias]['concrete'];
    }

    /**
     * Check if class is immutable.
     *
     * @param string $concrete
     *
     * @throws ContainerException
     */
    protected function notImmutable($concrete)
    {
        if (isset($this->immutable[$concrete])) {
            throw new ContainerException(sprintf('Attempted overwrite of initialized component [%s]', $concrete));
        }
    }

    /**
     * Drop all of the stale instances and aliases.
     *
     * @param string $alias
     */
    protected function dropStaleSingletons($alias)
    {
        unset($this->singletons[$alias], $this->aliases[$alias]);
    }

    /**
     * Determine if the given concrete is buildable.
     *
     * @param mixed  $concrete
     * @param string $alias
     *
     * @return bool
     */
    protected function isBuildable($concrete, $alias)
    {
        return $concrete === $alias || $concrete instanceof Closure;
    }

    /**
     * Extract the type and alias from a given definition.
     *
     * @param array $definition
     *
     * @return array
     */
    protected function extractAlias(array $definition)
    {
        return [key($definition), current($definition)];
    }

    /**
     * Check if the specified concrete and alias is a string.
     *
     * @param string|object|\Closure $alias
     * @param string|\Closure|null   $concrete
     *
     * @return bool
     */
    protected function isString($alias, $concrete)
    {
        $isNotObject = (is_string($alias) && (!is_object($concrete) && !$concrete instanceof Closure));

        return ($isNotObject && (is_string($concrete) || null !== $concrete));
    }

    /**
     * Normalize the given class name by removing leading slashes.
     *
     * @param mixed $service
     *
     * @return mixed
     */
    protected function normalize($service)
    {
        return is_string($service) ? ltrim($service, '\\') : $service;
    }

    /**
     * @return \Invoker\InvokerInterface
     */
    private function getInvoker()
    {
        if (!$this->invoker) {
            $container = [];

            foreach ($this->delegates as $delegate) {
                $container[] = new TypeHintContainerResolver($delegate);
            }

            $chain = [
                new NumericArrayResolver,
                new AssociativeArrayResolver,
                new DefaultValueResolver
            ];

            $parameterResolver = new ResolverChain(array_merge($container, $chain));

            $this->invoker = new Invoker($parameterResolver, $this);
        }

        return $this->invoker;
    }
}
