<?php
namespace Viserio\Container;

use Interop\Container\ContainerInterface as ContainerInteropInterface;

interface ContainerAwareInterface
{
    /**
     * Set a container.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function setContainer(ContainerInteropInterface $container);

    /**
     * Get the container.
     *
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer();
}
