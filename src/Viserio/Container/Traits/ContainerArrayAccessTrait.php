<?php
namespace Viserio\Container\Traits;

<<<<<<< HEAD
use Closure;

=======
/**
 * ContainerArrayAccessTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
>>>>>>> develop
trait ContainerArrayAccessTrait
{
    /**
     * {@inheritdoc}
     */
    public function set(string $alias, mixed $concrete)
    {
        return $this->bind($alias, $concrete);
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
     * @param string $alias Identifier of the entry to remove
     */
    public function remove($alias)
    {
        $this->offsetUnset($alias);
    }

    /**
     * Dynamically access application services.
     *
     * @param string $alias
     *
     * @return mixed
     */
    public function __get($alias)
    {
        return $this->offsetGet($alias);
    }

    /**
     * Dynamically set application services.
     *
     * @param string $alias
     * @param mixed  $concrete
     */
    public function __set($alias, $concrete)
    {
        $this->offsetSet($alias, $concrete);
    }

    /**
     * Dynamically check if application services exists.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function __isset($alias)
    {
        return $this->offsetExists($alias);
    }

    /**
     * Dynamically remove application services.
     *
     * @param string $alias
     */
    public function __unset($alias)
    {
        $this->offsetUnset($alias);
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $alias
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($alias)
    {
        $alias = $this->normalize($alias);

        if ($this->hasInDelegate($alias)) {
            return $this->getFromDelegate($alias);
        }

        if (!$this->isSingleton($alias) && isset($this->interopDefinitions[$alias])) {
            $this->singletons[$alias] = $this->resolveDefinition($this->interopDefinitions[$alias]);
        }

        return $this->make($alias);
    }

    /**
     * Sets a parameter or an object.
     *
     * @param string $alias
     * @param mixed  $concrete The value of the parameter or a closure to define an object
     *
     * @return self|null
     */
    public function offsetSet($alias, $concrete)
    {
        if (!$concrete instanceof Closure) {
            $concrete = function () use ($concrete) {
                return $concrete;
            };
        }

        $this->bind($alias, $concrete);
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function offsetExists($alias)
    {
        $alias = $this->normalize($alias);

        if (
            isset($this->keys[$alias]) ||
            isset($this->interopDefinitions[$alias])
        ) {
            return true;
        }

        return $this->hasInDelegate($alias);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $alias
     *
     * @return string|null $alias The unique identifier for the parameter or object
     */
    public function offsetUnset($alias)
    {
        $alias = $this->normalize($alias);

        if (isset($this->keys[$alias])) {
            unset(
                $this->aliases[$alias],
                $this->bindings[$alias],
                $this->singletons[$alias],
                $this->frozen[$alias],
                $this->values[$alias],
                $this->keys[$alias]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    abstract public function normalize($service);

    /**
     * {@inheritdoc}
     */
    abstract public function make($alias, array $args = []);

    /**
     * {@inheritdoc}
     */
    abstract public function bind($alias, $concrete = null, $singleton = false);
}
