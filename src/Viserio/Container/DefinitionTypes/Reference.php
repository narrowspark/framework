<?php
namespace Viserio\Container\DefinitionTypes;

use Interop\Container\Definition\AliasDefinitionInterface;

class Reference implements ReferenceInterface
{
    /**
     * @var string
     */
    private $target;

    /**
     * @param string $target
     */
    public function __construct($target)
    {
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
