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
     * @return self
     */
    public function needs(string $abstract): self;

    /**
     * Define the implementation for the contextual binding.
     *
     * @param \Closure|string $implementation
     */
    public function give($implementation);
}
