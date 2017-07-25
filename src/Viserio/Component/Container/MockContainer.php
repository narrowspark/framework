<?php
declare(strict_types=1);
namespace Viserio\Component\Container;

use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;

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
     * @param array $args
     *
     * @throws \InvalidArgumentException
     *
     * @return \Mockery\MockInterface
     */
    public function mock(...$args): MockInterface
    {
        $id = \array_shift($args);

        if (! $this->has($id)) {
            throw new InvalidArgumentException(\sprintf('Cannot mock a non-existent service: [%s]', $id));
        }

        $mock = 'mock::' . $id;

        if (! isset($this->mockedServices[$mock])) {
            $this->mockedServices[$mock] = \call_user_func_array([Mockery::class, 'mock'], $args);
        }

        return $this->mockedServices[$mock];
    }

    /**
     * Unset a mocked services.
     *
     * @param string $id
     */
    public function unmock(string $id): void
    {
        unset($this->mockedServices['mock::' . $id]);
    }

    /**
     * @return array
     */
    public function getMockedServices(): array
    {
        return $this->mockedServices;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->mockedServices['mock::' . $id] ?? parent::get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        if (isset($this->mockedServices['mock::' . $id])) {
            return true;
        }

        return parent::has($id);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->bindings[$offset], $this->mockedServices['mock::' . $offset]);
    }
}
