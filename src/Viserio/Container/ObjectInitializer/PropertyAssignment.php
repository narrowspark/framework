<?php
namespace Viserio\Container\ObjectInitializer;

use Interop\Container\Definition\ObjectInitializer\PropertyAssignmentInterface;
use Interop\Container\Definition\ReferenceInterface;

class PropertyAssignment implements PropertyAssignmentInterface
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var string|number|bool|array|ReferenceInterface
     */
    private $value;

    /**
     * @param string                                      $propertyName
     * @param string|number|bool|array|ReferenceInterface $value
     */
    public function __construct($propertyName, $value)
    {
        $this->propertyName = $propertyName;
        $this->value = $value;
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }

    public function getValue()
    {
        return $this->value;
    }
}
