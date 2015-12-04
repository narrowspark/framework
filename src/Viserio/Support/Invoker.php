<?php
namespace Viserio\Support;

use Interop\Container\ContainerInterface as ContainerContract;
use Invoker\Exception\InvocationException;
use Invoker\Invoker as DiInvoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Viserio\Support\Traits\ContainerAwareTrait;

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
     * Inject by type hint.
     *
     * @param bool $inject
     *
     * @return self
     */
    public function injectByTypeHint($inject = false)
    {
        $this->inject['type'] = $inject;

        return $this;
    }

    /**
     * Inject by parameter.
     *
     * @param bool $inject
     *
     * @return self
     */
    public function injectByParameterName($inject = false)
    {
        $this->inject['parameter'] = $inject;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function call($callable, array $parameters = [])
    {
        $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * Get a full configured invoker class.
     *
     * @return \Invoker\InvokerInterface
     */
    private function getInvoker()
    {
        if ($this->invoker === null && $this->container !== null) {

            $container = $this->getContainer();

            $resolvers = [
                new NumericArrayResolver,
                new AssociativeArrayResolver,
                new DefaultValueResolver,
            ];

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
