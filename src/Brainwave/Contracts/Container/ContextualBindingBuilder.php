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
 * ContextualBindingBuilder.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
interface ContextualBindingBuilder
{
    /**
     * Define the abstract target that depends on the context.
     *
     * @param string $abstract
     *
     * @return $this
     */
    public function needs($abstract);

    /**
     * Define the implementation for the contextual binding.
     *
     * @param \Closure|string $implementation
     */
    public function give($implementation);
}
