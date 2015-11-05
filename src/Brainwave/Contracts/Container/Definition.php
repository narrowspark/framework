<?php

namespace Brainwave\Contracts\Container;

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
 * Definition.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
interface Definition
{
    /**
     * Handle instansiation and manipulation of value and return
     *
     * @param  array $args
     *
     * @return mixed
     */
    public function __invoke(array $args = []);

    /**
     * Add an argument to be injected
     *
     * @param  mixed $arg
     *
     * @return self
     */
    public function withArgument($arg);

    /**
     * Add multiple arguments to be injected
     *
     * @param  array $args
     *
     * @return self
     */
    public function withArguments(array $args);
}
