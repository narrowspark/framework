<?php

namespace Brainwave\Container;

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

use Brainwave\Container\Exception\BindingResolutionException;
use Brainwave\Container\Exception\ContainerException;
use Brainwave\Container\Exception\NotFoundException;
use Brainwave\Container\Traits\ContainerArrayAccessTrait;
use Brainwave\Container\Traits\ContainerResolverTraits;
use Brainwave\Container\Traits\MockerContainerTrait;
use Brainwave\Contracts\Container\Container as ContainerContract;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Nucleus\Invoker\Invoker;

/**
 * Container.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Container implements \ArrayAccess, ContainerInteropInterface, ContainerContract
{
    /*
     * Array Access Support
     * Mock Support
     */
    use ContainerArrayAccessTrait, MockerContainerTrait, ContainerResolverTraits;

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
     * Array containing frozen instances.
     *
     * @var array
     */
    protected $frozen = [];

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
     * @var array
     */
    protected $inflectors = [];

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
     * @var \Nucleus\Invoker\Invoker
     */
    protected $invoker;

    /**
     *
     */
    public function __construct()
    {
        $this->invoker = new Invoker();
    }

    /**
     * Alias a type to a different name.
     *
     * @param string $alias
     * @param string $abstract
     */
    public function alias($alias, $abstract)
    {
        $this->keys[$alias] = true;
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
        $this->notFrozen($alias);

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
        if ($this->shouldNotBeDefinitionObject($alias, $concrete)) {
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

        // if the concrete is an already instantiated object, we just store it
        // as a singleton
        if ($this->shouldBeDefinitionObject($concrete)) {
            $concrete = new Definition($this, $concrete);
        }

        $this->bindings[$alias] = compact('concrete', 'singleton');

        return $this->bindings[$alias]['concrete'];
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $alias
     * @param array  $args
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function make($alias, array $args = [])
    {
        $alias = $this->getAlias($alias);

        if (!$this->bound($alias)) {
            throw new NotFoundException(sprintf('Binding [%s] does not exists in the container bindings', $alias));
        }

        // If an instance of the type is currently being managed as a singleton we'll
        // just return an existing instance instead of instantiating new instances
        // so the developer can keep using the same objects instance every time.
        if (isset($this->singletons[$alias])) {
            $this->frozen[$alias] = true;

            return $this->applyInflectors($this->singletons[$alias]);
        }

        if (isset($this->values[$alias])) {
            $this->frozen[$alias] = true;

            return $this->values[$alias];
        }

        $concrete = $this->getConcrete($alias);

        if ($this->isBuildable($concrete, $alias)) {
            $object = $this->build($concrete, $args);
        } else {
            $object = $this->make($concrete, $args);
        }

        // If the requested type is registered as a singleton we'll want to cache off
        // the instances in "memory" so we can return it later without creating an
        // entirely new instance of an object on each subsequent request for it.
        if ($this->isSingleton($alias)) {
            $this->singletons[$alias] = $object;
        }

        $this->frozen[$alias] = true;

        return $this->applyInflectors($object);
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
        if ($concrete instanceof \Closure) {
            return $concrete($this, $args);
        }

        $instances = $this->reflect($concrete, $args);

        return $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function inflector($type, callable $callback = null)
    {
        if (is_null($callback)) {
            $inflector = new Inflector();
            $this->inflectors[$type] = $inflector;

            return $inflector;
        }

        $this->inflectors[$type] = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function when($concrete)
    {
        $contextualBindingBuilder = new ContextualBindingBuilder($concrete);
        $contextualBindingBuilder->setContainer($this);

        return $contextualBindingBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function isRegistered($alias)
    {
        return isset($this->keys[$alias]);
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
        return isset($this->aliases[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleton($alias)
    {
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
     * @param array    $args
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function call($callable, array $args = [])
    {
        return $this->invoker->invoke($callable, $args);
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

        if (null === $boundObject) {
            throw new ContainerException(
                sprintf('Cannot extend %s because it has not yet been bound.', $binding)
            );
        }

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
        $this->contextual[$concrete][$alias] = $implementation;
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
        return isset($this->aliases[$alias]) ? $this->aliases[$alias] : $alias;
    }

    /**
     * Apply any active inflectors to the resolved object.
     *
     * @param object $object
     *
     * @return object
     */
    protected function applyInflectors($object)
    {
        foreach ($this->inflectors as $type => $inflector) {
            if (!$object instanceof $type) {
                continue;
            }

            if ($inflector instanceof Inflector) {
                $inflector->setContainer($this);
                $inflector->inflect($object);
                continue;
            }

            // must be dealing with a callable as the inflector
            call_user_func_array($inflector, [$object]);
        }

        return $object;
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

        // If we don't have a registered resolver or concrete for the type, we'll just
        // assume each type is a concrete name and will attempt to resolve it as is
        // since the container should be able to resolve concretes automatically.
        if (!isset($this->bindings[$alias])) {
            if (isset($this->bindings[$this->absoluteClassName($alias)])) {
                $alias = $this->absoluteClassName($alias);
            }

            return $alias;
        }

        return $this->bindings[$alias]['concrete'];
    }

    /**
     * Check if class is frozen.
     *
     * @param string $concrete
     *
     * @throws ContainerException
     */
    protected function notFrozen($concrete)
    {
        if (isset($this->frozen[$concrete])) {
            throw new ContainerException(sprintf('Cannot override frozen service [%s]', $concrete));
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
        return $concrete === $alias || $concrete instanceof \Closure;
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
     * Check if the specified concrete definiton should be a
     * definition object.
     *
     * @param string|object|\Closure $concrete The concrete definition
     *
     * @return bool
     */
    protected function shouldBeDefinitionObject($concrete)
    {
        return (
            is_object($concrete) && !$concrete instanceof \Closure || is_string($concrete)
        );
    }

    /**
     * Check if the specified concrete definiton should be not a
     * definition object.
     *
     * @param string|object|\Closure $alias
     * @param string|\Closure|null   $concrete
     *
     * @return bool
     */
    protected function shouldNotBeDefinitionObject($alias, $concrete)
    {
        return (
            (is_string($alias) && (!is_object($concrete) && !$concrete instanceof \Closure && (is_string($concrete) || null !== $concrete)))
        );
    }

    /**
     * Returns absolute class name - always with leading backslash.
     *
     * @param string $className
     *
     * @return string
     */
    protected function absoluteClassName($className)
    {
        return (substr($className, 0, 1) === '\\') ? $className : '\\'.$className;
    }
}
