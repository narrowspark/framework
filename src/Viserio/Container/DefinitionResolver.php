<?php
namespace Viserio\Container;

use Interop\Container\ContainerInterface;
use Interop\Container\Definition\AliasDefinitionInterface;
use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\DefinitionProviderInterface;
use Interop\Container\Definition\FactoryCallDefinitionInterface;
use Interop\Container\Definition\ParameterDefinitionInterface;
use Interop\Container\Definition\ReferenceInterface;
use ReflectionClass;
use Viserio\Container\Exception\EntryNotFound;
use Viserio\Container\Exception\InvalidDefinition;
use Viserio\Container\Exception\UnsupportedDefinition;

class DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve a definition and return the resulting value.
     *
     * @param DefinitionInterface $definition
     *
     * @throws UnsupportedDefinition
     * @throws EntryNotFound         A dependency was not found.
     *
     * @return mixed
     */
    public function resolveDefinition(DefinitionInterface $definition)
    {
        switch (true) {
            case $definition instanceof ParameterDefinitionInterface:
                return $definition->getValue();

            case $definition instanceof ObjectDefinition:
                $reflection = new ReflectionClass($definition->getClassName());

                // Create the instance
                $constructorArguments = $definition->getConstructorArguments();
                $constructorArguments = array_map([$this, 'resolveReference'], $constructorArguments);
                $service = $reflection->newInstanceArgs($constructorArguments);

                // Set properties
                $service = $this->callAssignments($service, $definition);

                // Call methods
                $service = $this->callMethods($service, $definition);

                if (array_key_exists($definition->getIdentifier(), $this->extensions)) {
                    foreach ($this->extensions[$definition->getIdentifier()] as $extension) {
                        $service = $this->callAssignments($service, $extension);
                        $service = $this->callMethods($service, $extension);
                    }
                }

                return $service;

            case $definition instanceof AliasDefinitionInterface:
                return $this->container->get($definition->getTarget());

            case $definition instanceof FactoryCallDefinitionInterface:
                $factory = $definition->getFactory();
                $methodName = $definition->getMethodName();
                $arguments = (array) $definition->getArguments();
                $arguments = array_map([$this, 'resolveReference'], $arguments);

                if (is_string($factory)) {
                    return call_user_func_array($factory . '::' . $methodName, $arguments);
                } elseif ($factory instanceof ReferenceInterface) {
                    $factory = $this->container->get($factory->getTarget());

                    return call_user_func_array([$factory, $methodName], $arguments);
                }

                throw new InvalidDefinition(sprintf('Definition "%s" does not return a valid factory'));

            default:
                throw UnsupportedDefinition::fromDefinition($definition);
        }
    }

    /**
     * Resolve a variable that can be a reference.
     *
     * @param ReferenceInterface|mixed $value
     *
     * @throws EntryNotFound The dependency was not found.
     *
     * @return mixed
     */
    private function resolveReference($value)
    {
        if ($value instanceof ReferenceInterface) {
            $value = $this->container->get($value->getTarget());
        }

        return $value;
    }

    /**
     * @param object                    $service
     * @param ObjectDefinitionInterface $definition
     *
     * @return object
     */
    private function callAssignments($service, ObjectDefinitionInterface $definition)
    {
        foreach ($definition->getPropertyAssignments() as $propertyAssignment) {
            $propertyName = $propertyAssignment->getPropertyName();
            $service->$propertyName = $this->resolveReference($propertyAssignment->getValue());
        }

        return $service;
    }

    /**
     * @param object                    $service
     * @param ObjectDefinitionInterface $definition
     *
     * @return object
     */
    private function callMethods($service, ObjectDefinitionInterface $definition)
    {
        foreach ($definition->getMethodCalls() as $methodCall) {
            $methodArguments = $methodCall->getArguments();
            $methodArguments = array_map([$this, 'resolveReference'], $methodArguments);
            call_user_func_array([$service, $methodCall->getMethodName()], $methodArguments);
        }

        return $service;
    }
}
