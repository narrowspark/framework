<?php
namespace Viserio\Support\Traits;

use Interop\Container\ContainerInterface as ContainerInteropInterface;
use RuntimeException;

trait ContainerAwareTrait
{
    /**
     * Container instance.
     *
     * @var \Interop\Container\ContainerInterface|null
     */
    protected $container;

    /**
     * Set a container.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return self
     */
    public function setContainer(ContainerInteropInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @throws \RuntimeException
     *
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer(): ContainerInteropInterface
    {
        if (! $this->container) {
            throw new RuntimeException('Container is not set up.');
        }

        return $this->container;
    }
}
