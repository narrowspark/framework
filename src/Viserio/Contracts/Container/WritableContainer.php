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
 * @version     0.10.0-dev
 */

/**
 * WritableContainer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
interface WritableContainer
{
    /**
     * Set a value on the container.
     * If the value is a callable, it will be called
     * on retrieval.
     * The value bound will be fetched at every
     * retrieval, if it is a callable, it will be
     * called every time as well
     *
     * @param string $alias
     * @param mixed  $concrete
     *
     * @return mixed
     */
    public function set(string $alias, mixed $concrete);

    /**
     * Set a value on the container
     * The value will always be the same upon retrieval
     * If it is a callable, it will only be called once
     *
     * @param string $alias
     * @param mixed  $concrete
     *
     * @return mixed
     */
    public function share(string $alias, mixed $concrete);
}
