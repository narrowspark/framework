<?php
namespace Viserio\Container;

class MockContainer extends Container
{
    /**
     * @var array
     */
    protected $mockedServices = [];

    /**
     * Takes an id of the service as the first argument.
     * Any other arguments are passed to the Mockery factory.
     *
     * @return \Mockery\Mock
     */
    public function mock()
    {
        $arguments = func_get_args();
        $id = array_shift($arguments);

        if (!$this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Cannot mock a non-existent service: "%s"', $id));
        }

        if (!array_key_exists($id, $this->mockedServices)) {
            $this->mockedServices['mock::'.$id] = call_user_func_array(['Mockery', 'mock'], $arguments);
        }

        return $this->mockedServices['mock::'.$id];
    }

    /**
     * Unset a mocked services.
     *
     * @param string $id
     */
    public function unmock($id)
    {
        unset($this->mockedServices['mock::'.$this->normalize($id)]);
    }

    /**
     * @return array
     */
    public function getMockedServices()
    {
        return $this->mockedServices;
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

        if (isset($this->mockedServices['mock::'.$alias])) {
            return $this->mockedServices['mock::'.$alias];
        }

        if ($this->hasInDelegate($alias)) {
            return $this->getFromDelegate($alias);
        }

        if (!$this->isSingleton($alias) && isset($this->interopDefinitions[$alias])) {
            $this->singletons[$alias] = $this->resolveDefinition($this->interopDefinitions[$alias]);
        }

        return $this->make($alias);
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
            isset($this->mockedServices['mock::'.$alias]) ||
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
                $this->keys[$alias],
                $this->mockedServices['mock::'.$alias]
            );
        }
    }
}
