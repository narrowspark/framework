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

use Interop\Container\Definition\ParameterDefinitionInterface;

/**
 * ParameterDefinition.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class ParameterDefinition extends NamedDefinition implements ParameterDefinitionInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $identifier
     * @param string $value
     */
    public function __construct($identifier, $value)
    {
        parent::__construct($identifier);
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
