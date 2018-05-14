<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler\Container;

use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Container\Exception\CyclicDependencyException;
use Viserio\Component\Contract\Container\Exception\NotFoundException;

abstract class CompiledContainer extends Container
{
    /**
     * The stack of concretions currently being built.
     *
     * @var array
     */
    protected $compiledBuildStack = [];

    /**
     * Private services are directly used in the compiled method.
     *
     * @var array
     */
    protected $privateServices = [];

    /**
     * The deprecated aliases.
     *
     * @var array
     */
    protected $deprecatedAliases = [];

    /**
     * List of registered methods.
     *
     * @var string[]
     */
    private static $methodMapping;

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $id = $this->getAlias($id);

        // If an instance of the type is currently being managed as a shared
        // we'll just return an existing instance.
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        $method = static::$methodMapping[$id] ?? null;

        // If it's a compiled entry, then there is a method in this class
        if ($method !== null) {
            if (\in_array($id, $this->compiledBuildStack, true)) {
                $this->compiledBuildStack[] = $id;

                throw new CyclicDependencyException($id, $this->compiledBuildStack);
            }

            $this->compiledBuildStack[] = $id;

            try {
                return $this->{$method}();
            } finally {
                \array_pop($this->compiledBuildStack);
            }
        }

        if ($resolved = $this->getFromDelegate($id)) {
            return $resolved;
        }

        throw new NotFoundException(
            \sprintf('Abstract [%s] is not being managed by the container.', $id)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        $this->ensureEntryIsString($id);
        $this->ensureEntryIsNotEmpty($id);

        $id = $this->getAlias($id);

        // The parent method is overridden to check in our array, it avoids resolving definitions
        if (isset(static::$methodMapping[$id])) {
            return true;
        }

        return parent::has($id);
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        parent::reset();

        $this->compiledBuildStack = [];
    }

    /**
     * {@inheritdoc}
     */
    public function make($abstract, array $parameters = [])
    {
        if (\is_string($abstract)) {
            $abstract = $this->getAlias($abstract);

            if (isset($this->services[$abstract])) {
                return $this->services[$abstract];
            }

            $method = static::$methodMapping[$abstract] ?? null;

            // If it's a compiled entry, then there is a method in this class
            if ($method !== null) {
                if (\in_array($abstract, $this->compiledBuildStack, true)) {
                    $this->compiledBuildStack[] = $abstract;

                    throw new CyclicDependencyException($abstract, $this->compiledBuildStack);
                }

                $this->compiledBuildStack[] = $abstract;

                try {
                    return $this->{$method}(...$parameters);
                } finally {
                    \array_pop($this->compiledBuildStack);
                }
            }
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAlias(string $id): string
    {
        if (! isset($this->aliases[$id])) {
            return parent::getAlias($id);
        }

        if (isset($this->deprecatedAliases[$id])) {
            @\trigger_error($this->deprecatedAliases[$id], \E_USER_DEPRECATED);
        }

        return $this->aliases[$id];
    }
}
