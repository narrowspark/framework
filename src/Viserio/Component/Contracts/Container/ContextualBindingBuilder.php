<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Container;

interface ContextualBindingBuilder
{
    /**
     * Define the abstract target that depends on the context.
     *
     * @param string $abstract
     *
     * @return $this
     */
    public function needs(string $abstract): ContextualBindingBuilder;

    /**
     * Define the implementation for the contextual binding.
     *
     * @param \Closure|string $implementation
     *
     * @throws \Viserio\Component\Contracts\Container\Exception\UnresolvableDependencyException
     *
     * @return void
     */
    public function give($implementation);
}
