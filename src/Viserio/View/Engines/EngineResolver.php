<?php
namespace Viserio\View\Engines;

use Closure;
use InvalidArgumentException;

class EngineResolver
{
    /**
     * The array of engine resolvers.
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * The resolved engine instances.
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * Register a new engine resolver.
     * The engine string typically corresponds to a file extension.
     *
     * @param string   $engine
     * @param \Closure $resolver
     */
    public function register(string $engine, Closure $resolver)
    {
        unset($this->resolved[$engine]);
        $this->resolvers[$engine] = $resolver;
    }

    /**
     * Resolver an engine instance by name.
     *
     * @param string $engine
     *
     * @return \Viserio\Contracts\View\Engines
     */
    public function resolve(string $engine): \Viserio\Contracts\View\Engines
    {
        if (isset($this->resolved[$engine])) {
            return $this->resolved[$engine];
        }

        if (isset($this->resolvers[$engine])) {
            return $this->resolved[$engine] = call_user_func($this->resolvers[$engine]);
        }

        throw new InvalidArgumentException('Engine ' . $engine . ' not found.');
    }
}
