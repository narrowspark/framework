<?php
namespace Viserio\Container\Traits;

/**
 * ContainerArrayAccessTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
trait ContainerArrayAccessTrait
{
    /**
     * Dynamically access application services.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Dynamically set application services.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * Dynamically check if application services exists.
     *
     * @param string $id
     *
     * @return bool
     */
    public function __isset($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * Dynamically remove application services.
     *
     * @param string $id
     */
    public function __unset($id)
    {
        $this->offsetUnset($id);
    }

    /**
     * Adds an entry to the container.
     *
     * @param string $id    Identifier of the entry to add
     * @param mixed  $value The entry to add to the container
     */
    public function set(string $id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $id
     */
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * Removes an entry from the container.
     *
     * @param string $id Identifier of the entry to remove
     */
    public function remove($id)
    {
        $this->offsetUnset($id);
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $id
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($id)
    {
        if (isset($this->mockedServices['mock::' . $id])) {
            return $this->mockedServices['mock::' . $id];
        }

        return $this->make($id);
    }

    /**
     * Sets a parameter or an object.
     *
     * @param string $id
     * @param mixed  $value The value of the parameter or a closure to define an object
     *
     * @return ContainerArrayAccessTrait|null
     */
    public function offsetSet($id, $value)
    {
        if (! $value instanceof \Closure) {
            $value = function () use ($value) {
                return $value;
            };
        }

        $this->bind($id, $value);
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $id
     *
     * @return bool
     */
    public function offsetExists($id)
    {
        if (isset($this->keys[$id]) || isset($this->mockedServices['mock::' . $id])) {
            return true;
        }

        return false;
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id
     *
     * @return string|null $id The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        if (isset($this->keys[$id])) {
            unset(
                $this->aliases[$id],
                $this->bindings[$id],
                $this->singletons[$id],
                $this->frozen[$id],
                $this->values[$id],
                $this->keys[$id],
                $this->mockedServices['mock::' . $id]
            );
        }
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $alias
     * @param array  $args
     *
     * @return mixed
     */
    abstract public function make($alias, array $args = []);

    /**
     * Register a binding with the container.
     *
     * @param string        $alias
     * @param \Closure|null $concrete
     * @param bool          $singleton
     */
    abstract public function bind($alias, $concrete = null, $singleton = false);
}
