<?php
namespace Viserio\Container\DefinitionTypes;

use Interop\Container\Definition\FactoryCallDefinitionInterface;
use Interop\Container\Definition\ReferenceInterface;

class FactoryCallDefinition extends NamedDefinition implements FactoryCallDefinitionInterface
{
    /**
     * The identifier of the instance in the container.
     *
     * @var string
     */
    private $identifier;

    /**
     * The fully qualified class name of this instance, or a fully qualified class name.
     *
     * @var ReferenceInterface|string
     */
    private $factory;

    /**
     * The name of the method to be called.
     *
     * @var string
     */
    private $methodName;

    /**
     * A list of arguments passed to the constructor.
     *
     * @var array Array of scalars or ReferenceInterface, or array mixing scalars, arrays, and ReferenceInterface
     */
    private $methodArguments = [];

    /**
     * Constructs an factory definition.
     *
     * @param string|null        $identifier      The identifier of the instance in the container. Can be null if the instance is anonymous (declared inline of other instances)
     * @param ReferenceInterface $factory         A pointer to the service that the factory method will be called upon, or a fully qualified class name
     * @param string             $methodName      The name of the factory method
     * @param array              $methodArguments The parameters of the factory method
     */
    public function __construct($identifier, $factory, $methodName, array $methodArguments = [])
    {
        $this->identifier = $identifier;
        $this->factory = $factory;
        $this->methodName = $methodName;
        $this->methodArguments = $methodArguments;
    }

    /**
     * Returns the identifier of the instance.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns a pointer to the service that the factory method will be called upon, or a fully qualified class name.
     *
     * @return ReferenceInterface|string
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Returns the name of the factory method.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Returns the parameters of the factory method.
     *
     * @return array
     */
    public function getMethodArguments()
    {
        return $this->methodArguments;
    }

    /**
     * Adds an argument to the method.
     *
     * @param mixed $argument
     */
    public function addMethodArgument($argument)
    {
        $this->methodArguments[] = $argument;
    }
}
