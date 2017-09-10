<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container;

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
     * @throws \Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException
     *
     * @return mixed
     */
    public function give($implementation);
}
