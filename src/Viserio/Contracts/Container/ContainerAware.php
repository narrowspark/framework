<?php
namespace Viserio\Contracts\Container;

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
 * @version     0.10.0
 */

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
    public function getContainer();
}
