<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\View;

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
     * @return \Viserio\Component\Contract\View\EngineResolver
     */
    public function register(string $engine, Closure $resolver): self;

    /**
     * Resolver an engine instance by name.
     *
     * @param string $engine
     *
     * @throws \InvalidArgumentException if no engine found
     *
     * @return \Viserio\Component\Contract\View\Engine
     */
    public function resolve(string $engine): Engine;
}
