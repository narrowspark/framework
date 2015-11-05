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
 * ClassDefinition.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
interface ClassDefinition extends Definition
{
    /**
     * Add a method to be invoked
     *
     * @param  string $method
     * @param  array  $args
     *
     * @return self
     */
    public function withMethodCall($method, array $args = []);
    /**
     * Add multiple methods to be invoked
     *
     * @param  array $methods
     *
     * @return self
     */
    public function withMethodCalls(array $methods = []);
}
