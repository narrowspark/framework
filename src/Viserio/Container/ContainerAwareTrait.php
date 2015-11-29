<?php
namespace Viserio\Container;

use Interop\Container\ContainerInterface as ContainerInteropInterface;

trait ContainerAwareTrait
{
    /**
     * @var \Interop\Container\ContainerInterface
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
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
