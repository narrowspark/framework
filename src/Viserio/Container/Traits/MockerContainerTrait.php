<?php
namespace Viserio\Container\Traits;

/**
 * ContainerAwareTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
trait MockerContainerTrait
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

        if (! $this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Cannot mock a non-existent service: "%s"', $id));
        }

        if (! array_key_exists($id, $this->mockedServices)) {
            $this->mockedServices['mock::' . $id] = call_user_func_array(['Mockery', 'mock'], $arguments);
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
        unset($this->mockedServices['mock::' . $id]);
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
    abstract public function has($id);
}
