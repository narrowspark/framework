<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engines;

use Closure;
use InvalidArgumentException;
use Viserio\Component\Contracts\View\Engine as EngineContract;
use Viserio\Component\Contracts\View\EngineResolver as EngineResolverContract;

class EngineResolver implements EngineResolverContract
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
     * {@inheritdoc}
     */
    public function register(string $engine, Closure $resolver): EngineResolverContract
    {
        unset($this->resolved[$engine]);

        $this->resolvers[$engine] = $resolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $engine): EngineContract
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
