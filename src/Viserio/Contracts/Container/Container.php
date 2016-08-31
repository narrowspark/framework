<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container;

use Closure;
use Interop\Container\ContainerInterface;

interface Container
{
    /**
     * Alias a type to a different name.
     *
     * @param string $abstract
     * @param string $alias
     */
    public function alias(string $abstract, string $alias);

    /**
     * Register a binding with the container.
     *
     * @param string|array         $abstract
     * @param \Closure|string|null $concrete
     */
    public function bind($abstract, $concrete = null);

    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     */
    public function bindIf(string $abstract, $concrete = null);

    /**
     * Register a shared binding in the container.
     *
     * @param string|array         $abstract
     * @param \Closure|string|null $concrete
     */
    public function singleton($abstract, $concrete = null);

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string|array $abstract
     * @param mixed        $instance
     */
    public function instance($abstract, $instance);

    /**
     * Delegate a backup container to be checked for services if it
     * cannot be resolved via this container.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return $this
     */
    public function delegate(ContainerInterface $container): Container;

    /**
     * Returns true if service is registered in one of the delegated backup containers.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function hasInDelegate(string $abstract): bool;

    /**
     * Removes an entry from the container.
     *
     * @param string $abstract Identifier of the entry to remove
     */
    public function forget(string $abstract);

    /**
     * Resolve the given type from the container.
     *
     * @param string $alias
     * @param array  $args
     *
     * @return mixed
     */
    public function make(string $abstract, array $parameters = []);

    /**
     * "Extend" an abstract type in the container.
     *
     * @param string   $abstract
     * @param \Closure $closure
     */
    public function extend(string $binding, Closure $closure);

    /**
     * Intercept the resolve call to add some features
     *
     * @param mixed $abstract
     * @param array $parameters
     *
     * @return mixed
     */
    public function resolve($abstract, array $parameters = []);

    /**
     * Resolve a bound type from container.
     *
     * @param string $abstract
     * @param array  $parameters
     *
     * @return mixed
     */
    public function resolveBound(string $abstract, array $parameters = []);

    /**
     * Resolve a non bound type.
     *
     * @param string $abstract
     * @param array  $parameters
     *
     * @return mixed
     */
    public function resolveNonBound(string $concrete, array $parameters = []);

    /**
     * Define a contextual binding.
     *
     * @param string $concrete
     *
     * @return \Viserio\Contracts\Container\ContextualBindingBuilder
     */
    public function when($concrete): ContextualBindingBuilder;
}
