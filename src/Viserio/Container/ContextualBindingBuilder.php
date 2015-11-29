<?php
namespace Viserio\Container;

use Viserio\Contracts\Container\ContextualBindingBuilder as ContextualBindingBuilderContract;

class ContextualBindingBuilder implements ContextualBindingBuilderContract
{
    use ContainerAwareTrait;

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
    protected $needs;

    /**
     * Create a new contextual binding builder.
     *
     * @param string $concrete
     */
    public function __construct($concrete)
    {
        $this->concrete = $concrete;
    }

    /**
     * Define the abstract target that depends on the context.
     *
     * @param string $abstract
     *
     * @return $this
     */
    public function needs($abstract)
    {
        $this->needs = $abstract;

        return $this;
    }

    /**
     * Define the implementation for the contextual binding.
     *
     * @param \Closure|string $implementation
     */
    public function give($implementation)
    {
        $this->getContainer()->addContextualBinding($this->concrete, $this->needs, $implementation);
    }
}
