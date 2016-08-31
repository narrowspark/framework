<?php
namespace Viserio\Container;

use Mockery\Mock;
use InvalidArgumentException;

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
    public function mock(): Mock
    {
        $arguments = func_get_args();
        $id = array_shift($arguments);

        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Cannot mock a non-existent service: "%s"', $id));
        }

        if (!array_key_exists($id, $this->mockedServices)) {
            $this->mockedServices['mock::' . $id] = call_user_func_array([Mock::class, 'mock'], $arguments);
        }

        return $this->mockedServices['mock::' . $id];
    }

    /**
     * Unset a mocked services.
     *
     * @param string $id
     */
    public function unmock($id)
    {
        unset($this->mockedServices['mock::' . $this->normalize($id)]);
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
     * @param string $offset
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($offset)
    {
        return $this->mockedServices['mock::' . $this->normalize($offset)] ?? $this->get($offset);
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        $offset = $this->normalize($offset);

        if (
            isset($this->mockedServices['mock::' . $offset]) ||
            isset($this->bindings[$offset])
        ) {
            return true;
        }

        return $this->hasInDelegate($offset);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $offset = $this->normalize($offset);

        if (isset($this->bindings[$offset])) {
            unset(
                $this->bindings[$offset],
                $this->mockedServices['mock::' . $offset]
            );
        }
    }
}
