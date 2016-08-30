<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container;

interface Container
{
    const TYPE_PLAIN = 0;

    const TYPE_SERVICE = 1;

    /**
     * A singleton entry will be computed once and shared.
     *
     * For a class, only a single instance of the class will be created.
     */
    const TYPE_SINGLETON = 2;

    const VALUE = 0;

    const IS_RESOLVED = 1;

    const BINDING_TYPE = 2;

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
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     */
    public function singleton($abstract, $concrete = null);

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed  $instance
     */
    public function instance(string $abstract, $instance);

    /**
     * Resolve the given type from the container.
     *
     * @param string $alias
     * @param array  $args
     *
     * @return mixed
     */
    public function make($alias, array $args = []);

    /**
     * Adds an entry to the container.
     *
     * @param string $id    Identifier of the entry to add
     * @param mixed  $value The entry to add to the container
     */
    public function set(string $id, $value);

    /**
     * Extend an existing binding.
     *
     * @param string   $binding The name of the binding to extend.
     * @param \Closure $closure The function to use to extend the existing binding.
     *
     * @throws ContainerException
     */
    public function extend(string $binding, \Closure $closure);

    /**
     * Removes an entry from the container.
     *
     * @param string $id Identifier of the entry to remove
     */
    public function remove(string $id);

    /**
     * Allows for methods to be invoked on any object that is resolved of the tyoe
     * provided.
     *
     * @param string        $type
     * @param callable|null $callback
     *
     * @return \Viserio\Container\Inflector|void
     */
    public function inflector(string $type, callable $callback = null);

    /**
     * Define a contextual binding.
     *
     * @param string $concrete
     *
     * @return \Viserio\Contracts\Container\ContextualBindingBuilder
     */
    public function when($concrete): \Viserio\Contracts\Container\ContextualBindingBuilder;

    /**
     * Check if an item is being managed as a singleton.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function isSingleton(string $alias): bool;
}
