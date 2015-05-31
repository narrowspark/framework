<?php

namespace Brainwave\Container;

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

use Brainwave\Contracts\Container\ContextualBindingBuilder as ContextualBindingBuilderContract;

/**
 * ContextualBindingBuilder.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class ContextualBindingBuilder implements ContextualBindingBuilderContract
{
    use ContainerAwareTrait;

    /**
     * The concrete instance.
     *
     * @var string
     */
    protected $concrete;

    /**
     * Abstract target.
     *
     * @var string
     */
    protected $needs;

    /**
     * Create a new contextual binding builder.
     *
     * @param string $concrete
     */
    public function __construct($concrete)
    {
        $this->concrete = $concrete;
    }

    /**
     * Define the abstract target that depends on the context.
     *
     * @param string $abstract
     *
     * @return $this
     */
    public function needs($abstract)
    {
        $this->needs = $abstract;

        return $this;
    }

    /**
     * Define the implementation for the contextual binding.
     *
     * @param \Closure|string $implementation
     */
    public function give($implementation)
    {
        $this->getContainer()->addContextualBinding($this->concrete, $this->needs, $implementation);
    }
}
