<?php
namespace Viserio\Container\DefinitionTypes;

use Interop\Container\Definition\AliasDefinitionInterface;

class AliasDefinition extends NamedDefinition implements AliasDefinitionInterface
{
    /**
     * @var string
     */
    private $target;

    /**
     * @param string $identifier
     * @param string $target
     */
    public function __construct($identifier, $target)
    {
        parent::__construct($identifier);
        $this->target = $target;
    }

    /**
     * Returns the name of the target container entry.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }
}
