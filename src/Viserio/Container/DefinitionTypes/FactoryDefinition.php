<?php
namespace Viserio\Container\DefinitionTypes;

use Interop\Container\Definition\FactoryDefinitionInterface;
use Interop\Container\Definition\ReferenceInterface;

class FactoryDefinition extends NamedDefinition implements FactoryDefinitionInterface
{
    /**
    * @var ReferenceInterface|string
    */
    private $factory;

    /**
    * @var string
    */
    private $methodName;

    /**
    * @var array
    */
    private $arguments = [];

    /**
    * @param string $identifier
    * @param ReferenceInterface|string $factory A reference to the service being called or a fully qualified class name for static calls
    * @param string $methodName
    */
    public function __construct($identifier, $factory, $methodName)
    {
       parent::__construct($identifier);
       $this->factory = $factory;
       $this->methodName = $methodName;
    }

    /**
    * Set the arguments to pass when calling the factory.
    *
    * @param string|number|bool|array|ReferenceInterface $argument Can be a scalar value or a reference to another entry.
    * @param string|number|bool|array|ReferenceInterface ...
    *
    * @return $this
    */
    public function setArguments($argument)
    {
       $this->arguments = func_get_args();
       return $this;
    }

    public function getFactory()
    {
       return $this->factory;
    }

    public function getMethodName()
    {
       return $this->methodName;
    }

    public function getArguments()
    {
       return $this->arguments;
    }
}
