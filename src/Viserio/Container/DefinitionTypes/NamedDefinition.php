<?php
namespace Viserio\Container\DefinitionTypes;

use Interop\Container\Definition\DefinitionInterface;

abstract class NamedDefinition implements DefinitionInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Create a reference to the current container entry.
     *
     * @return Reference
     */
    public function createReference()
    {
        return new Reference($this->getIdentifier());
    }
}
