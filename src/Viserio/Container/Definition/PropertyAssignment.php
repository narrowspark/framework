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

use Interop\Container\Definition\PropertyAssignmentInterface;
use Interop\Container\Definition\ReferenceInterface;

/**
 * PropertyAssignment.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
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
