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

use Interop\Container\Definition\FactoryDefinitionInterface;
use Interop\Container\Definition\ReferenceInterface;

/**
 * FactoryDefinition.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class FactoryDefinition extends NamedDefinition implements FactoryDefinitionInterface
{
    /**
     * @var \Interop\Container\Definition\ReferenceInterface
     */
    private $reference;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @param string $identifier
     * @param \Interop\Container\Definition\ReferenceInterface $reference
     * @param string $methodName
     */
    public function __construct($identifier, ReferenceInterface $reference, $methodName)
    {
        parent::__construct($identifier);
        $this->reference = $reference;
        $this->methodName = $methodName;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }
}
