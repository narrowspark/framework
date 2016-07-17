<?php
namespace Viserio\Contracts\View;

use Closure;

interface EngineResolver
{
    /**
     * Register a new engine resolver.
     * The engine string typically corresponds to a file extension.
     *
     * @param string   $engine
     * @param \Closure $resolver
     *
     * @return $this
     */
    public function register(string $engine, Closure $resolver): EngineResolver;

    /**
     * Resolver an engine instance by name.
     *
     * @param string $engine
     *
     * @return \Viserio\Contracts\View\Engine
     */
    public function resolve(string $engine): Engine;
}
