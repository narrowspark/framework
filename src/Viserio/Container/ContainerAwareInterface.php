<?php
namespace Viserio\Container;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Interop\Container\ContainerInterface as ContainerInteropInterface;

/**
 * ContainerAwareInterface.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
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
