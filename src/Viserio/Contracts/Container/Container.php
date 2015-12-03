<?php
namespace Viserio\Contracts\Container;

/**
 * Container.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Container
{
    /**
     * Alias a type to a different name.
     *
     * @param string $abstract
     * @param string $alias
     */
    public function alias($abstract, $alias);

    /**
     * Register a binding with the container.
     *
     * @param string               $alias
     * @param \Closure|string|null $concrete
     * @param bool                 $singleton
     */
    public function bind($alias, $concrete = null, $singleton = false);

    /**
     * Register a shared binding in the container.
     *
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     */
    public function singleton($abstract, $concrete = null);

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
     * @param string    $id    Identifier of the entry to add
     * @param \stdClass $value The entry to add to the container
     */
    public function set($id, $value);

    /**
     * Extend an existing binding.
     *
     * @param string   $binding The name of the binding to extend.
     * @param \Closure $closure The function to use to extend the existing binding.
     *
     * @throws ContainerException
     */
    public function extend($binding, \Closure $closure);

    /**
     * Removes an entry from the container.
     *
     * @param string $id Identifier of the entry to remove
     */
    public function remove($id);

    /**
     * Allows for methods to be invoked on any object that is resolved of the tyoe
     * provided.
     *
     * @param string        $type
     * @param callable|null $callback
     *
     * @return \Viserio\Container\Inflector|void
     */
    public function inflector($type, callable $callback = null);

    /**
     * Define a contextual binding.
     *
     * @param string $concrete
     *
     * @return \Viserio\Contracts\Container\ContextualBindingBuilder
     */
    public function when($concrete);

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function bound($abstract);

    /**
     * Check if an item is being managed as a singleton.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function isSingleton($alias);

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
    public function call($callable, array $args = []);
}
