<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler\Container;

use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Viserio\Component\Container\Container;
use Viserio\Component\Container\Invoker\FactoryParameterResolver;
use Viserio\Component\Contract\Container\Exception\ContainerException;
use Viserio\Component\Contract\Container\Exception\CyclicDependencyException;

abstract class CompiledContainer extends Container
{
    /**
     * A InvokerInterface implementation.
     *
     * @var null|\Invoker\InvokerInterface
     */
    private $factoryInvoker;

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (! \is_string($id)) {
            throw new ContainerException(\sprintf(
                'The id parameter must be of type string, [%s] given.',
                \is_object($id) ? \get_class($id) : \gettype($id)
            ));
        }

        if (isset($this->resolvedEntries[$id])) {
            return $this->resolvedEntries[$id];
        }

        $method = static::$methodMapping[$id] ?? null;

        // If it's a compiled entry, then there is a method in this class
        if ($method !== null) {
            if (\in_array($id, $this->buildStack, true)) {
                $this->buildStack[] = $id;

                throw new CyclicDependencyException($id, $this->buildStack);
            }

            $this->buildStack[] = $id;

            try {
                $value = $this->$method();
            } finally {
                \array_pop($this->buildStack);
            }

            // Store the entry to always return it without recomputing it
            $this->resolvedEntries[$id] = $value;

            return $value;
        }

        return parent::get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        if (! \is_string($id)) {
            throw new ContainerException(\sprintf(
                'The id parameter must be of type string, [%s] given.',
                \is_object($id) ? \get_class($id) : \gettype($id)
            ));
        }

        // The parent method is overridden to check in our array, it avoids resolving definitions
        if (isset(static::$methodMapping[$id])) {
            return true;
        }

        return parent::has($id);
    }

    /**
     * Get a configured instance of factory invoker.
     *
     * @return \Invoker\InvokerInterface
     */
    protected function getFactoryInvoker(): InvokerInterface
    {
        if (! $this->factoryInvoker) {
            $parameterResolver = [
                new AssociativeArrayResolver(),
                new FactoryParameterResolver($this),
                new NumericArrayResolver(),
                new DefaultValueResolver(),
            ];

            $this->factoryInvoker = new Invoker(new ResolverChain($parameterResolver), $this);
        }

        return $this->factoryInvoker;
    }
}
