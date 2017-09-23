<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container;

use ArrayAccess;
use Closure;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface, Factory, ArrayAccess
{
    /**
     * Alias a type to a different name.
     *
     * @param string $abstract
     * @param string $alias
     *
     * @return void
     */
    public function alias(string $abstract, string $alias): void;

    /**
     * Register a binding with the container.
     *
     * @param array|string         $abstract
     * @param null|\Closure|string $concrete
     *
     * @return void
     */
    public function bind($abstract, $concrete = null): void;

    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param string               $abstract
     * @param null|\Closure|string $concrete
     *
     * @return void
     */
    public function bindIf(string $abstract, $concrete = null): void;

    /**
     * Register a shared binding in the container.
     *
     * @param string               $abstract
     * @param null|\Closure|string $concrete
     *
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void;

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed  $instance
     *
     * @return void
     */
    public function instance(string $abstract, $instance): void;

    /**
     * Delegate a backup container to be checked for services if it
     * cannot be resolved via this container.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return $this
     */
    public function delegate(ContainerInterface $container): Container;

    /**
     * Returns true if service is registered in one of the delegated backup containers.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function hasInDelegate(string $abstract): bool;

    /**
     * Removes an entry from the container.
     *
     * @param string $abstract Identifier of the entry to remove
     *
     * @return void
     */
    public function forget(string $abstract): void;

    /**
     * "Extend" an abstract type in the container.
     *
     * @param string   $binding
     * @param \Closure $closure
     *
     * @return void
     */
    public function extend(string $binding, Closure $closure): void;

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
     * @param \Closure|string $abstract
     * @param array           $parameters
     *
     * @return mixed
     */
    public function resolveNonBound($abstract, array $parameters = []);

    /**
     * Define a contextual binding.
     *
     * @param string $concrete
     *
     * @return $this
     */
    public function when(string $concrete): Container;

    /**
     * Registers a service provider.
     *
     * @param \Interop\Container\ServiceProviderInterface $provider   the service provider to register
     * @param array                              $parameters An array of values that customizes the provider
     *
     * @return $this
     */
    public function register(ServiceProviderInterface $provider, array $parameters = []): Container;

    /**
     * Check if a binding is computed.
     *
     * @param array $binding
     *
     * @return bool
     */
    public function isComputed($binding): bool;

    /**
     * Return all added bindings.
     *
     * @return array
     */
    public function getBindings(): array;
}
