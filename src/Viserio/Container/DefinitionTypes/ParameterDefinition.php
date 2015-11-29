<?php
namespace Viserio\Container\DefinitionTypes;

use Interop\Container\Definition\ParameterDefinitionInterface;

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
