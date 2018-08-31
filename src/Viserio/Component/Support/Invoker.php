<?php
declare(strict_types=1);
namespace Viserio\Component\Support;

use Invoker\Invoker as DiInvoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ParameterResolver;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\ParameterResolver\TypeHintResolver;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;

final class Invoker implements InvokerInterface
{
    use ContainerAwareTrait;

    /**
     * Inject settings.
     *
     * @var array
     */
    private $inject = [];

    /**
     * Array of all added resolvers.
     *
     * @var array
     */
    private $resolvers = [];

    /**
     * Invoker instance.
     *
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * Get a full configured invoker class.
     *
     * @return \Invoker\InvokerInterface
     */
    private function getInvoker(): InvokerInterface
    {
        if ($this->invoker === null) {
            $resolvers = \array_merge([
                new AssociativeArrayResolver(),
                new NumericArrayResolver(),
                new TypeHintResolver(),
                new DefaultValueResolver(),
            ], $this->resolvers);

            if (($container = $this->container) !== null) {
                if (isset($this->inject['type'])) {
                    $resolvers[] = new TypeHintContainerResolver($container);
                }

                if (isset($this->inject['parameter'])) {
                    $resolvers[] = new ParameterNameContainerResolver($container);
                }

                $this->invoker = new DiInvoker(new ResolverChain($resolvers), $container);
            } else {
                $this->invoker = new DiInvoker(new ResolverChain($resolvers));
            }
        }

        return $this->invoker;
    }

    /**
     * Inject by type hint.
     *
     * @param bool $inject
     *
     * @return $this
     */
    public function injectByTypeHint(bool $inject = false): self
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
    public function injectByParameterName(bool $inject = false): self
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
    public function addResolver(ParameterResolver $resolver): self
    {
        $this->resolvers[] = $resolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function call($callable, array $parameters = [])
    {
        return $this->getInvoker()->call($callable, $parameters);
    }
}
