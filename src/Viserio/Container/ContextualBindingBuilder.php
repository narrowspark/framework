<?php
declare(strict_types=1);
namespace Viserio\Container;

use Closure;
use ReflectionClass;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Container\Exceptions\UnresolvableDependencyException;
use Viserio\Container\Traits\NormalizeClassNameTrait;
use Viserio\Contracts\Container\ContextualBindingBuilder as ContextualBindingBuilderContract;

class ContextualBindingBuilder implements ContextualBindingBuilderContract
{
    use ContainerAwareTrait;
    use NormalizeClassNameTrait;

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
    protected $parameter;

    /**
     * Contextual parameters.
     *
     * @var array
     */
    protected $contextualParameters = [];

    /**
     * Create a new contextual binding builder.
     *
     * @param string $concrete
     */
    public function __construct(string $concrete)
    {
        $this->concrete = $concrete;
    }

    /**
     * {@inheritdoc}
     */
    public function needs(string $abstract): ContextualBindingBuilderContract
    {
        $this->parameter = $this->normalize($abstract);

        if ($this->parameter[0] === '$') {
            $this->parameter = substr($this->parameter, 1);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function give($implementation)
    {
        if (!($reflector = $this->getContainer()->getReflector($this->concrete))) {
            throw new UnresolvableDependencyException("[$this->concrete] is not resolvable.");
        }

        if ($reflector instanceof ReflectionClass && !($reflector = $reflector->getConstructor())) {
            throw new UnresolvableDependencyException("[$this->concrete] must have a constructor.");
        }

        $reflectionParameters = $reflector->getParameters();
        $contextualParameters = &$this->contextualParameters[$this->concrete];

        foreach ($reflectionParameters as $key => $parameter) {
            $class = $parameter->getClass();

            if ($this->parameter === $parameter->name) {
                return $contextualParameters[$key] = $implementation;
            }

            if ($class && $this->parameter === $class->name) {
                return $contextualParameters[$key] = $this->contextualBindingFormat($implementation, $class);
            }
        }

        throw new UnresolvableDependencyException("Parameter [$this->parameter] cannot be injected in [$this->concrete].");
    }

    /**
     * Format a class binding
     *
     * @param string|closure|object $implementation
     * @param ReflectionClass       $parameterClass
     *
     * @return closure|object
     */
    private function contextualBindingFormat($implementation, ReflectionClass $parameter)
    {
        if ($implementation instanceof Closure || $implementation instanceof $parameter->name) {
            return $implementation;
        }

        return function($container) use ($implementation) {
            return $container->make($implementation);
        };
    }
}
