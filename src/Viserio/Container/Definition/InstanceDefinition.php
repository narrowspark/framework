<?php
namespace Viserio\Container\Definition;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Interop\Container\Definition\InstanceDefinitionInterface;
use Interop\Container\Definition\MethodCallInterface;
use Interop\Container\Definition\PropertyAssignmentInterface;

/**
 * InstanceDefinition.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class InstanceDefinition extends NamedDefinition implements InstanceDefinitionInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $constructorArguments = [];

    /**
     * @var PropertyAssignmentInterface[]
     */
    private $propertyAssignments = [];

    /**
     * @var MethodCallInterface[]
     */
    private $methodCalls = [];

    /**
     * @param string $identifier
     * @param string $className
     */
    public function __construct($identifier, $className)
    {
        parent::__construct($identifier);
        $this->className = $className;
    }

    /**
     * @param scalar|\Interop\Container\Definition\ReferenceInterface $argument
     */
    public function addConstructorArgument($argument)
    {
        $this->constructorArguments[] = $argument;
    }

    public function addPropertyAssignment(PropertyAssignmentInterface $propertyAssignment)
    {
        $this->propertyAssignments[] = $propertyAssignment;
    }

    public function addMethodCall(MethodCallInterface $methodCall)
    {
        $this->methodCalls[] = $methodCall;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getConstructorArguments()
    {
        return $this->constructorArguments;
    }

    /**
     * @return PropertyAssignmentInterface[]
     */
    public function getPropertyAssignments()
    {
        return $this->propertyAssignments;
    }

    /**
     * @return MethodCallInterface[]
     */
    public function getMethodCalls()
    {
        return $this->methodCalls;
    }
}
