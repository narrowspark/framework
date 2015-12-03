<?php
namespace Viserio\Contracts\Container;

/**
 * ContextualBindingBuilder.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
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
