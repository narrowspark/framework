<?php
namespace Viserio\Contracts\Container;

/**
 * ContainerAware.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
interface ContainerAware
{
    /**
     * Set a container.
     *
     * @param $container
     */
    public function setContainer($container);

    /**
     * Get the container.
     *
     * @return \Viserio\Contracts\Container\Container
     */
    public function getContainer(): Container;
}
