<?php
namespace Viserio\Container;

use Interop\Container\ContainerInterface as ContainerInteropInterface;

/**
 * ContainerAwareTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
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
     */
    public function setContainer(ContainerInteropInterface $container)
    {
        $this->container = $container;
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
