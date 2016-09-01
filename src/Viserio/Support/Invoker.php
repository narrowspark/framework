<?php
declare(strict_types=1);
namespace Viserio\Support;

use Invoker\Invoker as DiInvoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ParameterResolver;
use Invoker\ParameterResolver\ResolverChain;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;

class Invoker implements InvokerInterface
{
    use ContainerAwareTrait;

    /**
     * Invoker instance.
     *
     * @var InvokerInterface
     */
    protected $invoker;

    /**
     * Inject settings.
     *
     * @var array
     */
    protected $inject = [];

    /**
     * Array of all added resolvers.
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * Inject by type hint.
     *
     * @param bool $inject
     *
     * @return $this
     */
    public function injectByTypeHint(bool $inject = false): InvokerInterface
    {
        $this->inject['type'] = $inject;

        return $this;
    }

    /**
     * Inject by parameter.
     *
     * @param bool $inject
     *
     * @return $this
     */
    public function injectByParameterName(bool $inject = false): InvokerInterface
    {
        $this->inject['parameter'] = $inject;

        return $this;
    }

    /**
     * Adds a resolver to the invoker class.
     *
     * @param ParameterResolver $resolver
     *
     * @return $this
     */
    public function addResolver(ParameterResolver $resolver): InvokerInterface
    {
        $this->resolvers[] = $resolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function call($callable, array $parameters = [])
    {
        $this->getInvoker();

        return $this->invoker->call($callable, $parameters);
    }

    /**
     * Get a full configured invoker class.
     *
     * @return \Invoker\InvokerInterface
     */
    private function getInvoker(): InvokerInterface
    {
        if ($this->invoker === null && $this->container !== null) {
            $container = $this->container;

            $resolvers = array_merge([
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new DefaultValueResolver(),
            ], $this->resolvers);

            if (isset($this->inject['type'])) {
                $resolvers[] = new TypeHintContainerResolver($container);
            }

            if (isset($this->inject['parameter'])) {
                $resolvers[] = new ParameterNameContainerResolver($container);
            }

            $this->invoker = new DiInvoker(new ResolverChain($resolvers), $container);
        }

        return $this->invoker;
    }
}
