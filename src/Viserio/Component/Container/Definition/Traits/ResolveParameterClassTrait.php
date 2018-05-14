<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition\Traits;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Viserio\Component\Container\Reflection\ReflectionFactory;

trait ResolveParameterClassTrait
{
    /**
     * A container implementation.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function resolveParameterClass(string $class): object
    {
        if ($this->container !== null && $this->container->has($class)) {
            return $this->container->get($class);
        }

        return $this->resolveReflectionClass(
            $reflectionClass = ReflectionFactory::getClassReflector($class),
            ReflectionFactory::getParameters($reflectionClass)
        );
    }

    /**
     * @see ReflectionResolver::resolveReflectionClass()
     *
     * @param ReflectionClass $reflectionClass
     * @param array           $reflectionParameters
     * @param array           $parameters
     *
     * @return object
     */
    abstract protected function resolveReflectionClass(
        ReflectionClass $reflectionClass,
        array $reflectionParameters,
        array $parameters = []
    ): object;
}
