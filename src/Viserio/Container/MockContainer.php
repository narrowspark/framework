<?php
declare(strict_types=1);
namespace Viserio\Container;

use InvalidArgumentException;
use Mockery;

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
     * @return \Mockery
     */
    public function mock()
    {
        $arguments = func_get_args();
        $id = array_shift($arguments);

        if (! $this->has($id)) {
            throw new InvalidArgumentException(sprintf('Cannot mock a non-existent service: "%s"', $id));
        }

        if (! isset($this->mockedServices['mock::' . $id])) {
            $this->mockedServices['mock::' . $id] = call_user_func_array([Mockery::class, 'mock'], $arguments);
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
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->mockedServices['mock::' . $this->normalize($id)] ?? parent::get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        if (isset($this->mockedServices['mock::' . $this->normalize($this->normalize($id))])) {
            return true;
        }

        return parent::has($id);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $offset = $this->normalize($offset);

        unset($this->bindings[$offset], $this->mockedServices['mock::' . $offset]);
    }
}
