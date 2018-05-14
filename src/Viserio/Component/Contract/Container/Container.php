<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container;

use ArrayAccess;
use Closure;
use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface, Factory, ArrayAccess
{
    /**
     * Sets the instantiator to be used when fetching proxies.
     *
     * @param \Viserio\Component\Contract\Container\Instantiator $proxyInstantiator
     *
     * @return void
     */
    public function setInstantiator(Instantiator $proxyInstantiator): void;

    /**
     * Set a object definition lazy.
     *
     * @param string $abstract
     *
     * @return void
     */
    public function setLazy(string $abstract): void;

    /**
     * Check if definition is lazy.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function isLazy(string $abstract): bool;

    /**
     * Register a binding with the container.
     *
     * @param string               $abstract
     * @param null|\Closure|string $concrete
     *
     * @return void
     */
    public function bind(string $abstract, $concrete = null): void;

    /**
     * Register a shared binding in the container.
     *
     * Sometimes, you may wish to bind something into the container that should only be resolved once
     * and the same instance should be returned on subsequent calls into the container.
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
     * You may also bind an existing object instance into the container using the instance method.
     * The given instance will always be returned on subsequent calls into the container.
     *
     * @param string $abstract
     * @param mixed  $instance
     *
     * @return void
     */
    public function instance(string $abstract, $instance): void;

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
     * Get the extender callbacks for a given type.
     *
     * @param string $abstract
     *
     * @return array
     */
    public function getExtenders(string $abstract): array;

    /**
     * Remove all of the extender callbacks for a given type.
     *
     * @param string $abstract
     *
     * @return void
     */
    public function forgetExtenders(string $abstract): void;

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
     * Determine if the given binding has been resolved.
     *
     * @param string $id
     *
     * @return bool
     */
    public function isResolved(string $id): bool;

    /**
     * Return all added definitions.
     *
     * @return \Viserio\Component\Contract\Container\Compiler\Definition[]
     */
    public function getDefinitions(): array;

    /**
     * Clear the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function reset(): void;
}
