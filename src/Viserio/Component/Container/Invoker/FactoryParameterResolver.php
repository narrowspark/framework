<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Invoker;

use Invoker\ParameterResolver\ParameterResolver;
use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;

/**
 * Inject the container, the definition or any other service using type-hints.
 *
 * {@internal This class is similar to TypeHintingResolver and TypeHintingContainerResolver,
 *            we use this instead for performance reasons}
 *
 * Code in this class it taken from php-di.
 *
 * See the original here: https://github.com/PHP-DI/PHP-DI/blob/master/src/Invoker/FactoryParameterResolver.php
 *
 * @author Matthieu Napoli https://github.com/mnapoli
 * @copyright Copyright (c) Matthieu Napoli
 */
class FactoryParameterResolver implements ParameterResolver
{
    /**
     * A ContainerInterface implementation.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * Create a new FactoryParameterResolver instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(ReflectionFunctionAbstract $reflection, array $providedParameters, array $resolvedParameters): array
    {
        $parameters = $reflection->getParameters();

        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $parameters = array_diff_key($parameters, $resolvedParameters);
        }

        foreach ($parameters as $index => $parameter) {
            $parameterClass = $parameter->getClass();

            if (! $parameterClass) {
                continue;
            }

            if ($parameterClass->name === ContainerInterface::class) {
                $resolvedParameters[$index] = $this->container;
            } elseif ($this->container->has($parameterClass->name)) {
                $resolvedParameters[$index] = $this->container->get($parameterClass->name);
            }
        }

        return $resolvedParameters;
    }
}
